<?php

namespace Track\Support;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{

    /**
     * 集合所有元素
     *
     * @var array
     */
    protected $items = [];

    /**
     * 创建集合
     *
     * @param  mixed $items
     *
     * @return void
     */
    public function __construct( $items = [] )
    {
        $this->items = $this->getArrayableItems( $items );
    }

    /**
     * 根据参数,获取他的数组式访问的结构
     *
     * @param $items
     *
     * @return array|mixed
     */
    protected function getArrayableItems( $items )
    {
        if ( is_array( $items ) ) {
            return $items;
        } elseif ( $items instanceof self ) {
            return $items->all();
        } elseif ( $items instanceof JsonSerializable ) {
            return $items->jsonSerialize();
        }

        return (array)$items;
    }

    /**
     * 获取所有项
     *
     * @return array|mixed
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * 遍历集合,对每个值调用回调,并返回一个新集合
     *
     * @param callable $callback
     *
     * @return Collection
     */
    public function map( callable $callback )
    {
        $keys = array_keys( $this->items );

        $items = array_map( $callback, $this->items, $keys );

        return new static( array_combine( $keys, $items ) );
    }

    /**
     * 返回数组的第一个元素
     *
     * @param  callable|null $callback
     * @param  mixed         $default
     *
     * @return mixed
     */
    public function first( callable $callback = null, $default = null )
    {
        return Arr::first( $this->items, $callback, $default );
    }

    /**
     * 键是否在集合中
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function contains( $key )
    {
        if ( $this->useAsCallable( $key ) ) {
            $placeholder = new \stdClass();

            return $this->first( $key, $placeholder ) !== $placeholder;
        }

        return in_array( $key, $this->items );
    }

    /**
     * value 是否可调用
     *
     * @param  mixed $value
     *
     * @return bool
     */
    protected function useAsCallable( $value )
    {
        return ! is_string( $value ) && is_callable( $value );
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param  callable|mixed  $callback
     * @return static
     */
    public function reject($callback)
    {
        if ($this->useAsCallable($callback)) {
            return $this->filter(function ($value, $key) use ($callback) {
                return ! $callback($value, $key);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the values of a given key.
     *
     * @param  string|array  $value
     * @param  string|null  $key
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }

    public function offsetExists( $offset )
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet( $offset )
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet( $offset, $value )
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset( $offset )
    {
        // TODO: Implement offsetUnset() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }


}
