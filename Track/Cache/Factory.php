<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/5/31
 * Time: 13:57
 */

namespace Track\Cache;


interface Factory
{
    /**
     * 获取缓存实例
     *
     * @param null $name
     *
     * @return Repository
     */
    public function store( $name = null );
}