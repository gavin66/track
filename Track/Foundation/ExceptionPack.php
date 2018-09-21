<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/15
 * Time: 12:04
 */

namespace Track\Foundation;

/**
 * 包装异常
 *
 * @package Track\Foundation
 */
class ExceptionPack implements \JsonSerializable
{

    /**
     * 异常
     *
     * @var \Exception
     */
    private $exception;

    /**
     * 异常格式
     *
     * @var string
     */
    private $format = '($exception$ (code: $code$) $message$ at $file$:$line$)' . PHP_EOL . '<stacktrace>' . PHP_EOL . '$stacktrace$' . PHP_EOL;

    /**
     * 将异常转换为字符串
     *
     * @var string
     */
    private $str;

    public function __construct( \Exception $exception )
    {
        $this->exception = $exception;
    }

    protected function toStr()
    {
        $this->str = str_replace( '$exception$', get_class( $this->exception ), $this->format );
        $this->str = str_replace( '$code$', $this->exception->getCode(), $this->str );
        $this->str = str_replace( '$message$', $this->exception->getMessage(), $this->str );
        $this->str = str_replace( '$file$', $this->exception->getFile(), $this->str );
        $this->str = str_replace( '$line$', $this->exception->getLine(), $this->str );
        $this->str = str_replace( '$stacktrace$', $this->exception->getTraceAsString(), $this->str );

        return $this->str;
    }

    public function jsonSerialize()
    {
        return $this->toStr();
    }

}