<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 13:55
 */

namespace Track\Database\Connectors;


interface ConnectorInterface
{
    /**
     * 连接数据库
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect( array $config );
}