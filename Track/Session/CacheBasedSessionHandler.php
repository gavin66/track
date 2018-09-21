<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/25
 * Time: 11:32
 */

namespace Track\Session;


use Track\Cache\RepositoryInterface as Cache;

class CacheBasedSessionHandler implements \SessionHandlerInterface
{

    /**
     * 缓存存储实例
     *
     * @var Cache
     */
    protected $cache;

    /**
     * 生存时间
     *
     * @var int
     */
    protected $minutes;

    public function __construct( Cache $cache, $minutes )
    {
        $this->cache   = $cache;
        $this->minutes = $minutes;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function destroy( $session_id )
    {
        return $this->cache->forget( $session_id );
    }

    /**
     * @inheritdoc
     */
    public function gc( $maxlifetime )
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function open( $save_path, $name )
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read( $session_id )
    {
        return $this->cache->get( $session_id, '' );
    }

    /**
     * @inheritdoc
     */
    public function write( $session_id, $session_data )
    {
        $this->cache->put( $session_id, $session_data, $this->minutes );
    }

    /**
     * 获取缓存的实例
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

}