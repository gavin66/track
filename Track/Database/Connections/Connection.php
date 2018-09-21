<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/30
 * Time: 11:28
 */

namespace Track\Database\Connections;


use Track\Database\Concerns\DetectsDeadlocks;
use Track\Database\Concerns\DetectsLostConnections;
use Track\Database\Concerns\ManagesTransactions;
use Track\Database\Exceptions\QueryException;
use Closure;
use LogicException;
use Exception;
use Track\Facades\Log;
use Track\Support\Arr;
use PDO;
use PDOStatement;

class Connection implements ConnectionInterface
{
    use
        // 死锁错误
        DetectsDeadlocks,
        // 连接断开错误
        DetectsLostConnections,
        // 事务支持
        ManagesTransactions;

    /**
     * pdo 连接
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * 连接的数据库名称
     *
     * @var string
     */
    protected $database;

    /**
     * 表前缀
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * 激活的事务次数
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * 数据库连接配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * 重新连接
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * 是否对数据库进行了修改
     *
     * @var bool
     */
    protected $recordsModified = false;

    /**
     * 已经解析的连接实例
     *
     * @var array
     */
    protected static $resolvers = [];

    public function __construct( $pdo, $database = '', $tablePrefix = '', array $config = [] )
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;
    }

    /**
     * 获取一条数据
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     * @throws Exception
     */
    public function selectOne( $query, $bindings = [] )
    {
        $records = $this->select( $query, $bindings );

        return array_shift( $records );
    }

    /**
     * 执行 select 查询语句
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     */
    public function select( $query, $bindings = [] )
    {
        return $this->run( $query, $bindings, function ( $query, $bindings ) {
            $statement = $this->prepared( $this->getPdo()->prepare( $query ) );

            $this->bindValues( $statement, $bindings );

            $statement->execute();

            return $statement->fetchAll();
        } );
    }

    /**
     * 执行 insert 插入语句
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return bool
     * @throws Exception
     */
    public function insert( $query, $bindings = [] )
    {
        return $this->statement( $query, $bindings );
    }

    /**
     * 执行 update 更新语句
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function update( $query, $bindings = [] )
    {
        return $this->affectingStatement( $query, $bindings );
    }

    /**
     * 执行 delete 删除语句
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function delete( $query, $bindings = [] )
    {
        return $this->affectingStatement( $query, $bindings );
    }

    /**
     * 运行 SQL 语句,返回 bool 结果
     *
     * @param       $query
     * @param array $bindings
     *
     * @return bool
     * @throws Exception
     */
    public function statement( $query, $bindings = [] )
    {
        return $this->run( $query, $bindings, function ( $query, $bindings ) {

            $statement = $this->getPdo()->prepare( $query );

            $this->bindValues( $statement, $bindings );

            return $statement->execute();
        } );
    }

    /**
     * 运行 SQL 语句,返回影响行数
     *
     * @param  string $query
     * @param  array  $bindings
     *
     * @return int
     */
    public function affectingStatement( $query, $bindings = [] )
    {
        return $this->run( $query, $bindings, function ( $query, $bindings ) {
            $statement = $this->getPdo()->prepare( $query );

            $this->bindValues( $statement, $bindings );

            $statement->execute();

            $this->recordsHaveBeenModified(
                ( $count = $statement->rowCount() ) > 0
            );

            return $count;
        } );
    }

    /**
     * 执行 SQL 语句
     *
     * @param string  $query
     * @param array   $bindings
     * @param Closure $callback
     *
     * @return mixed
     * @throws QueryException
     */
    protected function run( $query, $bindings, Closure $callback )
    {
        $this->reconnectIfMissingConnection();

        try {
            $result = $this->runQueryCallback( $query, $bindings, $callback );
        } catch ( QueryException $exception ) {
            $result = $this->handleQueryException( $exception, $query, $bindings, $callback );
        }

        return $result;
    }

    /**
     * 运行 SQL 语句,闭包
     *
     * @param         $query
     * @param         $bindings
     * @param Closure $callback
     *
     * @return mixed
     * @throws QueryException
     */
    protected function runQueryCallback( $query, $bindings, Closure $callback )
    {
        try {
            $result = $callback( $query, $bindings );
        } catch ( Exception $exception ) {
            throw new QueryException( $query, $bindings, $exception );
        }

        return $result;
    }

    /**
     * 处理查询异常
     *
     * @param QueryException $exception
     * @param                $query
     * @param                $bindings
     * @param Closure        $callback
     *
     * @return mixed
     * @throws QueryException
     */
    protected function handleQueryException( QueryException $exception, $query, $bindings, Closure $callback )
    {
        // 如果已经在事务中,抛出异常
        if ( $this->transactions >= 1 ) {
            throw $exception;
        }

        // 如果是因为数据库连接中断,那么重新连接并执行
        return $this->tryAgainIfCausedByLostConnection( $exception, $query, $bindings, $callback );
    }

    /**
     * 处理查询过程中的查询异常
     *
     * @param QueryException $e
     * @param                $query
     * @param                $bindings
     * @param Closure        $callback
     *
     * @return mixed
     *
     * @throws QueryException
     */
    protected function tryAgainIfCausedByLostConnection( QueryException $e, $query, $bindings, Closure $callback )
    {
        if ( $this->causedByLostConnection( $e->getPrevious() ) ) {
            $this->reconnect();

            return $this->runQueryCallback( $query, $bindings, $callback );
        }

        throw $e;
    }

    /**
     * 生成预处理语句
     *
     * @param PDOStatement $statement
     *
     * @return PDOStatement
     */
    protected function prepared( PDOStatement $statement )
    {
        $statement->setFetchMode( $this->fetchMode );

        return $statement;
    }

    /**
     * 如果连接失效,重新连接
     */
    protected function reconnectIfMissingConnection()
    {
        if ( is_null( $this->getPdo() ) ) {
            $this->reconnect();
        }
    }

    /**
     * 关闭数据库连接
     */
    public function disconnect()
    {
        $this->setPdo( null );
    }

    /**
     * 重新连接数据库
     *
     * @return mixed
     */
    public function reconnect()
    {

        if ( is_callable( $this->reconnector ) ) {
            Log::info( '数据库连接丢失,但已重新连接' );

            return call_user_func( $this->reconnector, $this );
        }

        throw new LogicException( '数据库连接丢失并且重连不可用' );
    }

    /**
     * 获取 PDO
     *
     * @return \PDO
     */
    public function getPdo()
    {
        if ( $this->pdo instanceof \Closure ) {
            return $this->pdo = call_user_func( $this->pdo );
        }

        return $this->pdo;
    }

    /**
     * 设置 PDO 连接
     *
     * @param $pdo
     *
     * @return $this
     */
    public function setPdo( $pdo )
    {
        $this->transactions = 0;

        $this->pdo = $pdo;

        return $this;
    }

    /**
     * 重新连接
     *
     * @param callable $reconnector
     *
     * @return $this
     */
    public function setReconnector( callable $reconnector )
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * 绑定 statement 的参数
     *
     * @param \PDOStatement $statement
     * @param array         $bindings
     */
    public function bindValues( $statement, $bindings )
    {
        foreach ( $bindings as $key => $value ) {
            $statement->bindValue(
                is_string( $key ) ? $key : $key + 1, $value,
                is_int( $value ) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * 数据是否已经被修改
     *
     * @param bool $value
     */
    public function recordsHaveBeenModified( $value = true )
    {
        if ( ! $this->recordsModified ) {
            $this->recordsModified = $value;
        }
    }

    /**
     * 获取当前数据连接名称
     *
     * @return mixed
     */
    public function getName()
    {
        return Arr::get( $this->config, 'name' );
    }

    /**
     * @param $driver
     *
     * @return mixed|null
     */
    public static function getResolver( $driver )
    {
        return isset( static::$resolvers[ $driver ] ) ? static::$resolvers[ $driver ] : null;
    }

    /**
     * 获取当前连接的数据库
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

}