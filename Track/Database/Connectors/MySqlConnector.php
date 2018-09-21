<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 14:22
 */

namespace Track\Database\Connectors;


class MySqlConnector extends Connector implements ConnectorInterface
{
    /**
     * 创建 Mysql 连接
     *
     * @param array $config
     *
     * @return \PDO
     * @throws \Exception
     */
    public function connect( array $config )
    {
        $dsn = $this->getDsn( $config );

        $options = $this->getOptions( $config );

        $connection = $this->createConnection( $dsn, $config, $options );

        if ( ! empty( $config[ 'database' ] ) ) {
            $connection->exec( "use `{$config['database']}`" );
        }

        $this->configureEncoding( $connection, $config );

        $this->configureTimezone( $connection, $config );

        $this->setModes( $connection, $config );

        return $connection;
    }

    protected function getDsn( array $config )
    {
        extract( $config, EXTR_SKIP );

        return isset( $port ) ? "mysql:host={$host};port={$port};dbname={$database}" : "mysql:host={$host};dbname={$database}";
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     *
     * @return mixed
     */
    protected function configureEncoding( $connection, array $config )
    {
        if ( ! isset( $config[ 'charset' ] ) ) {
            return $connection;
        }

        $connection->prepare( "set names '{$config['charset']}'" . $this->getCollation( $config ) )->execute();
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getCollation( array $config )
    {
        return isset( $config[ 'collation' ] ) ? " collate '{$config['collation']}'" : '';
    }

    /**
     * @param  \PDO $connection
     * @param array $config
     */
    protected function configureTimezone( $connection, array $config )
    {
        if ( isset( $config[ 'timezone' ] ) ) {
            $connection->prepare( 'set time_zone="' . $config[ 'timezone' ] . '"' )->execute();
        }
    }

    /**
     * 模式
     *
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setModes( $connection, array $config )
    {
        if ( isset( $config[ 'strict' ] ) ) {
            if ( $config[ 'strict' ] ) {
                $connection->prepare( $this->strictMode() )->execute();
            } else {
                $connection->prepare( "set session sql_mode='NO_ENGINE_SUBSTITUTION'" )->execute();
            }
        }
    }

    /**
     * 严格模式
     *
     * @return string
     */
    protected function strictMode()
    {
        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }

}