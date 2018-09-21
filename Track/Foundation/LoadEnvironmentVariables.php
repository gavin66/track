<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/28
 * Time: 14:13
 */

namespace Track\Foundation;


use Track\Application;
use Track\Foundation\Exceptions\InvalidPathException;

class LoadEnvironmentVariables
{

    /**
     * @var Application
     */
    private $app;

    public function bootstrap( Application $app )
    {
        $this->app = $app;
        $this->load();
    }

    private function load()
    {
        $envFilePath = $this->app->basePath() . DIRECTORY_SEPARATOR . '.env';
        if ( ! is_readable( $envFilePath ) || ! is_file( $envFilePath ) ) {
            throw new InvalidPathException( sprintf( '不能读取环境配置文件 %s', $envFilePath ) );
        }

        // 自动换行读取文件(auto_detect_line_endings=On 适用于 Macintosh 电脑),此操作会有很小的性能损失
        $autodetect = ini_get( 'auto_detect_line_endings' );
        ini_set( 'auto_detect_line_endings', 'On' );
        $lines = file( $envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        ini_set( 'auto_detect_line_endings', $autodetect );

        foreach ( $lines as $line ) {
            if ( ! ( strpos( ltrim( $line ), '#' ) === 0 ) && ( strpos( $line, '=' ) !== false ) ) {
                if ( function_exists( 'putenv' ) ) {
                    putenv( "$line" );
                }
            }
        }

    }
}