<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/28
 * Time: 14:34
 */

namespace Track\Foundation;


use Symfony\Component\Finder\Finder;
use Track\Application;
use Track\Config\Repository;

class LoadConfiguration
{
    /**
     * 启动
     *
     * @param Application $app
     *
     * @throws \Exception
     */
    public function bootstrap( Application $app )
    {
        $item = [];

        $app->instance( 'config', $config = new Repository( $item ) );

        $this->loadConfigurationFiles( $app, $config );

        date_default_timezone_set( $config->get( 'app.timezone', 'UTC' ) );

        mb_internal_encoding( 'UTF-8' );
    }

    /**
     * 加载 config 下的所有配置文件
     *
     * @param \Track\Application       $app
     * @param \Track\Config\Repository $repository
     *
     * @throws \Exception
     */
    protected function loadConfigurationFiles( Application $app, Repository $repository )
    {
        $files = $this->getConfigurationsFiles( $app );

        if ( ! isset( $files[ 'app' ] ) ) {
            throw new \Exception( '读取不到 app 配置文件' );
        }

        foreach ( $files as $key => $path ) {
            $repository->set( $key, require $path );
        }
    }

    /**
     * 获取 config 下的所有配置文件
     *
     * @param \Track\Application $app
     *
     * @return array
     */
    protected function getConfigurationsFiles( Application $app )
    {
        $files = [];

        $configPath = realpath( $app->configPath() );

        foreach ( Finder::create()->files()->name( '*.php' )->in( $configPath ) as $file ) {
            $directory = $this->getNestedDirectory( $file, $configPath );

            $files[ $directory . basename( $file->getRealPath(), '.php' ) ] = $file->getRealPath();
        }

        ksort( $files, SORT_NATURAL );

        return $files;
    }

    /**
     * 获取嵌套的配置
     *
     * @param \SplFileInfo $file
     * @param              $configPath
     *
     * @return string
     */
    protected function getNestedDirectory( \SplFileInfo $file, $configPath )
    {
        $directory = $file->getPath();

        if ( $nested = trim( str_replace( $configPath, '', $directory ), DIRECTORY_SEPARATOR ) ) {
            $nested = str_replace( DIRECTORY_SEPARATOR, '.', $nested ) . '.';
        }

        return $nested;
    }

}