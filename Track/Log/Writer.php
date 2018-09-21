<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/9
 * Time: 12:17
 */

namespace Track\Log;

/**
 * 记录日志
 *
 * Class Logger
 */
class Writer
{
    /**
     * 日志存放目录
     *
     * @var string
     */
    protected $logPath;

    /**
     * 调试信息日志
     *
     * @param   string $message
     * @param          $context
     */
    public function debug( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function info( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function notice( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function warning( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function error( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function critical( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function alert( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    public function emergency( $message, $context = [] )
    {
        $this->log( strtoupper( __FUNCTION__ ), $message, $context );
    }

    /**
     * 写入日志
     *
     * @param $level
     * @param $message
     * @param $context
     */
    protected function log( $level, $message, $context = [] )
    {
        $content = sprintf( "%s\t%s\t%s\t%s\n", date( 'Y-m-d H:i:s' ), $level, $message, $this->stringify( $context ) );

        file_put_contents( $this->getFilePath( $level ), $content, FILE_APPEND );
    }

    /**
     * 获取日志文件路径
     *
     * @param $level
     *
     * @return string
     */
    protected function getFilePath( $level )
    {
        $logFile = sprintf( $this->logPath . "%s_%s.log", date( 'Y-m-d' ), $level );

        if ( file_exists( $this->logPath ) === false ) {
            // todo 如果没有写入权限就是失败了
            mkdir( $this->logPath, 0777, true );
        }

        return $logFile;
    }

    /**
     * 设置日志存放路径
     *
     * @param string $logPath
     */
    public function setLogPath( $logPath )
    {
        $this->logPath = is_dir( $logPath ) ? $logPath : $logPath . DIRECTORY_SEPARATOR;
    }

    /**
     * 美化数据
     *
     * @param $data
     *
     * @return mixed
     */
    public function stringify( $data )
    {
        return $this->replaceNewlines( $this->convertToString( $data ) );
    }

    /**
     * 换行
     *
     * @param $str
     *
     * @return mixed
     */
    protected function replaceNewlines( $str )
    {
        if ( 0 === strpos( $str, '{' ) ) {
            return str_replace( [ '\r', '\n' ], [ "\r", "\n" ], $str );
        }

        return str_replace( [ "\r\n", "\r", "\n" ], ' ', $str );
    }

    /**
     * 转换字符串
     *
     * @param $data
     *
     * @return mixed|string
     */
    protected function convertToString( $data )
    {
        if ( null === $data || is_bool( $data ) ) {
            return var_export( $data, true );
        }

        if ( is_scalar( $data ) ) {
            return (string)$data;
        }

        return json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}