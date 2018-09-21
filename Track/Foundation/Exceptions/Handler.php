<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/11
 * Time: 16:12
 */

namespace Track\Foundation\Exceptions;

use Exception;
use Track\Container\ContainerContract as Container;
use Track\Foundation\ExceptionPack;
use Track\Http\Exceptions\HttpException;
use Track\Http\JsonResponse;
use Track\Http\Request;
use Track\Http\Response;
use Track\Routing\Router;
use Track\Support\Arr;

class Handler implements HandlerContract
{
    /**
     * 服务容器
     *
     * @var Container
     */
    protected $container;

    /**
     * 自定义异常,不报告
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * 内部异常,不报告
     *
     * @var array
     */
    protected $internalDontReport = [
        HttpException::class,
    ];


    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * 报告错误,写入日志
     *
     * @param Exception $exception
     *
     * @return mixed
     * @throws Exception
     */
    public function report( Exception $exception )
    {
        if ( $this->shouldntReport( $exception ) ) {
            return null;
        }

        if ( method_exists( $exception, 'report' ) ) {
            return $exception->report();
        }

        $this->container->make( 'log' )->error( $exception->getMessage(), [ 'exception' => new ExceptionPack( $exception ) ] );
    }

    /**
     * 给定的异常是否不需要报告
     *
     * @param  \Exception $e
     *
     * @return bool
     */
    protected function shouldntReport( Exception $e )
    {
        $dontReport = array_merge( $this->dontReport, $this->internalDontReport );

        return ! is_null( Arr::first( $dontReport, function ( $type ) use ( $e ) {
            return $e instanceof $type;
        } ) );
    }

    /**
     * 生成页面响应
     *
     * @param Request   $request
     * @param Exception $exception
     *
     * @return JsonResponse|Response
     */
    public function render( Request $request, Exception $exception )
    {
        if ( method_exists( $exception, 'render' ) && $response = $exception->render( $request ) ) {
            return Router::toResponse( $request, $response );
        }

        return $request->expectsJson() ? $this->prepareJsonResponse( $exception ) : $this->prepareResponse( $exception );
    }

    /**
     * json 响应
     *
     * @param Exception $exception
     *
     * @return JsonResponse
     */
    protected function prepareJsonResponse( Exception $exception )
    {
        $status = $this->isHttpException( $exception ) ? $exception->getStatusCode() : 500;

        $headers = $this->isHttpException( $exception ) ? $exception->getHeaders() : [];

        return new JsonResponse( $this->convertExceptionToArray( $exception ), $status, $headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    }

    /**
     * html 响应
     *
     * @param Exception $exception
     *
     * @return Response
     */
    protected function prepareResponse( Exception $exception )
    {
        $status = $this->isHttpException( $exception ) ? $exception->getStatusCode() : 500;

        $headers = $this->isHttpException( $exception ) ? $exception->getHeaders() : [];

        return new Response( $this->getHtml( FlattenException::create( $exception, $status ) ), $status, $headers );
    }

    /**
     * 异常转换为数组
     *
     * @param Exception $e
     *
     * @return array
     */
    protected function convertExceptionToArray( Exception $e )
    {
        return config( 'app.debug' ) ? [
            'message'   => $e->getMessage(),
            'exception' => get_class( $e ),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => collect( $e->getTrace() )->map( function ( $trace ) {
                return Arr::except( $trace, [ 'args' ] );
            } )->all(),
        ] : [
            'message' => $this->isHttpException( $e ) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * 是否是 http 异常
     *
     * @param Exception $e
     *
     * @return bool
     */
    protected function isHttpException( Exception $e )
    {
        return $e instanceof HttpException;
    }

    /**
     * 获取异常的 HTML 展示代码
     *
     * @param FlattenException $exception
     *
     * @return string
     */
    private function getHtml( FlattenException $exception )
    {
        $css     = $this->getStylesheet();
        $content = $this->getContent( $exception );

        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="robots" content="noindex,nofollow" />
        <style>$css</style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }

    private function getContent( FlattenException $exception )
    {
        switch ( $exception->getStatusCode() ) {
            case 404:
                $title = '当前页面未找到';
                break;
            default:
                $title = '出错了';
        }

        $content = '';
        if ( config( 'app.debug' ) ) {
            try {
                $count = count( $exception->getAllPrevious() );
                $total = $count + 1;
                foreach ( $exception->toArray() as $position => $e ) {
                    $ind     = $count - $position + 1;
                    $class   = $this->formatClass( $e[ 'class' ] );
                    $message = nl2br( $this->escapeHtml( $e[ 'message' ] ) );
                    $content .= sprintf( <<<'EOF'
                        <div class="trace trace-as-html">
                            <table class="trace-details">
                                <thead class="trace-head"><tr><th>
                                    <h3 class="trace-class">
                                        <span class="text-muted">(%d/%d)</span>
                                        <span class="exception_title">%s</span>
                                    </h3>
                                    <p class="break-long-words trace-message">%s</p>
                                </th></tr></thead>
                                <tbody>
EOF
                        , $ind, $total, $class, $message );
                    foreach ( $e[ 'trace' ] as $trace ) {
                        $content .= '<tr><td>';
                        if ( $trace[ 'function' ] ) {
                            $content .= sprintf( 'at <span class="trace-class">%s</span><span class="trace-type">%s</span><span class="trace-method">%s</span>(<span class="trace-arguments">%s</span>)', $this->formatClass( $trace[ 'class' ] ), $trace[ 'type' ], $trace[ 'function' ], $this->formatArgs( $trace[ 'args' ] ) );
                        }
                        if ( isset( $trace[ 'file' ] ) && isset( $trace[ 'line' ] ) ) {
                            $content .= $this->formatPath( $trace[ 'file' ], $trace[ 'line' ] );
                        }
                        $content .= "</td></tr>\n";
                    }

                    $content .= "</tbody>\n</table>\n</div>\n";
                }
            } catch ( \Exception $e ) {
                // something nasty happened and we cannot throw an exception anymore
                if ( $this->debug ) {
                    $title = sprintf( 'Exception thrown when handling an exception (%s: %s)', get_class( $e ), $this->escapeHtml( $e->getMessage() ) );
                } else {
                    $title = 'Whoops, looks like something went wrong.';
                }
            }
        }

        return <<<EOF
            <div class="exception-summary">
                <div class="container">
                    <div class="exception-message-wrapper">
                        <h1 class="break-long-words exception-message">$title</h1>
                    </div>
                </div>
            </div>

            <div class="container">
                $content
            </div>
EOF;
    }

    private function formatClass( $class )
    {
        $parts = explode( '\\', $class );

        return sprintf( '<abbr title="%s">%s</abbr>', $class, array_pop( $parts ) );
    }

    private function formatPath( $path, $line )
    {
        $file = $this->escapeHtml( preg_match( '#[^/\\\\]*+$#', $path, $file ) ? $file[ 0 ] : $path );

        return sprintf( '<span class="block trace-file-path">in <a title="%s line %3$d"><strong>%s</strong> (line %d)</a></span>', $this->escapeHtml( $path ), $file, $line );
    }

    /**
     * @param $str
     *
     * @return string
     */
    private function escapeHtml( $str )
    {
        return htmlspecialchars( $str, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8' );
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    private function formatArgs( array $args )
    {
        $result = [];
        foreach ( $args as $key => $item ) {
            if ( 'object' === $item[ 0 ] ) {
                $formattedValue = sprintf( '<em>object</em>(%s)', $this->formatClass( $item[ 1 ] ) );
            } elseif ( 'array' === $item[ 0 ] ) {
                $formattedValue = sprintf( '<em>array</em>(%s)', is_array( $item[ 1 ] ) ? $this->formatArgs( $item[ 1 ] ) : $item[ 1 ] );
            } elseif ( 'null' === $item[ 0 ] ) {
                $formattedValue = '<em>null</em>';
            } elseif ( 'boolean' === $item[ 0 ] ) {
                $formattedValue = '<em>' . strtolower( var_export( $item[ 1 ], true ) ) . '</em>';
            } elseif ( 'resource' === $item[ 0 ] ) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace( "\n", '', $this->escapeHtml( var_export( $item[ 1 ], true ) ) );
            }

            $result[] = is_int( $key ) ? $formattedValue : sprintf( "'%s' => %s", $this->escapeHtml( $key ), $formattedValue );
        }

        return implode( ', ', $result );
    }

    private function getStylesheet()
    {
        return <<<'EOF'
            body { background-color: #F9F9F9; color: #222; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding-bottom: 45px; }

            a { cursor: pointer; text-decoration: none; }
            a:hover { text-decoration: underline; }
            abbr[title] { border-bottom: none; cursor: help; text-decoration: none; }

            code, pre { font: 13px/1.5 Consolas, Monaco, Menlo, "Ubuntu Mono", "Liberation Mono", monospace; }

            table, tr, th, td { background: #FFF; border-collapse: collapse; vertical-align: top; }
            table { background: #FFF; border: 1px solid #E0E0E0; box-shadow: 0px 0px 1px rgba(128, 128, 128, .2); margin: 1em 0; width: 100%; }
            table th, table td { border: solid #E0E0E0; border-width: 1px 0; padding: 8px 10px; }
            table th { background-color: #E0E0E0; font-weight: bold; text-align: left; }

            .hidden-xs-down { display: none; }
            .block { display: block; }
            .break-long-words { -ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; }
            .text-muted { color: #999; }

            .container { max-width: 1024px; margin: 0 auto; padding: 0 15px; }
            .container::after { content: ""; display: table; clear: both; }

            .exception-summary { background: #B0413E; border-bottom: 2px solid rgba(0, 0, 0, 0.1); border-top: 1px solid rgba(0, 0, 0, .3); flex: 0 0 auto; margin-bottom: 30px; }

            .exception-message-wrapper { display: flex; align-items: center; min-height: 70px; }
            .exception-message { flex-grow: 1; padding: 30px 0; }
            .exception-message, .exception-message a { color: #FFF; font-size: 21px; font-weight: 400; margin: 0; }
            .exception-message.long { font-size: 18px; }
            .exception-message a { border-bottom: 1px solid rgba(255, 255, 255, 0.5); font-size: inherit; text-decoration: none; }
            .exception-message a:hover { border-bottom-color: #ffffff; }

            .exception-illustration { flex-basis: 111px; flex-shrink: 0; height: 66px; margin-left: 15px; opacity: .7; }

            .trace + .trace { margin-top: 30px; }
            .trace-head .trace-class { color: #222; font-size: 18px; font-weight: bold; line-height: 1.3; margin: 0; position: relative; }

            .trace-message { font-size: 14px; font-weight: normal; margin: .5em 0 0; }

            .trace-file-path, .trace-file-path a { color: #222; margin-top: 3px; font-size: 13px; }
            .trace-class { color: #B0413E; }
            .trace-type { padding: 0 2px; }
            .trace-method { color: #B0413E; font-weight: bold; }
            .trace-arguments { color: #777; font-weight: normal; padding-left: 2px; }

            @media (min-width: 575px) {
                .hidden-xs-down { display: initial; }
            }
EOF;
    }

}