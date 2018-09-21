<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/24
 * Time: 18:12
 */

namespace Track\Database\Exceptions;

use PDOException;
use Track\Support\Str;

class QueryException extends PDOException
{
    protected $sql;

    /**
     * 语句绑定的参数
     *
     * @var array
     */
    protected $bindings;

    /**
     * QueryException constructor.
     *
     * @param            $sql
     * @param            $bindings
     * @param \Exception $previous
     */
    public function __construct( $sql, $bindings, $previous )
    {
        parent::__construct( '', 0, $previous );

        $this->sql      = $sql;
        $this->bindings = $bindings;
        $this->code     = $previous->getCode();
        $this->message  = $this->formatMessage( $sql, $bindings, $previous );

        if ( $previous instanceof PDOException ) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * @param            $sql
     * @param            $binding
     * @param \Exception $previous
     *
     * @return string
     */
    protected function formatMessage( $sql, $binding, $previous )
    {
        return $previous->getMessage() . '(SQL: ' . Str::replaceArray( '?', $binding, $sql ) . ')';
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

}