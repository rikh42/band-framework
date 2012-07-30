<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\cache;
use snb\cache\CacheInterface;
use snb\core\ContainerAware;

/**
 * MemcachedCache
 * A cache class that uses Memcached to perform the actual caching
 */
class MemcachedCache extends ContainerAware implements CacheInterface
{
    protected $memCached;
    protected $cacheKeyPrefix;

    public function __construct($host, $port, $prefix)
    {
        // Key prefix to avoid clashes with other apps
        $this->cacheKeyPrefix = $prefix;

        // Create a connection to memcached
        $this->memCached = new \Memcached();
        $this->memCached->addServer($host, $port);
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        // If we don't have memCached, then fail
        if (!$this->memCached) {
            return null;
        }

        // Try and find the object and check if it worked
        $object = $this->memCached->get(md5($this->cacheKeyPrefix.$key));
        if ($this->memCached->getResultCode() != \Memcached::RES_SUCCESS) {
            return null;
        }

        return $object;
    }


    /**
     * @param $key
     */
    public function remove($key)
    {
        // If we don't have memCached, then fail
        if (!$this->memCached) {
            return;
        }

        // Try and find the object and check if it worked
        $key = md5($this->cacheKeyPrefix.$key);
        $this->memCached->delete($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire - time in seconds to keep it in the cache
     */
    public function set($key, $value, $expire = 60)
    {
        // If we don't have memCached, then fail
        if (!$this->memCached) {
            return;
        }

        // we do not permit items to never expire (they clog the cache)
        $expire = ($expire===0) ? 60*60*8 : $expire;

        // Stuff the item into the cache
        $this->memCached->set(md5($this->cacheKeyPrefix.$key), $value, $expire);
    }


    /**
     * @param $key
     * @param  int $amount
     * @return int
     */
    public function increment($key, $amount=1)
    {
        // If we don't have memCached, then fail
        if (!$this->memCached) {
            return 0;
        }

        // prepare the key and increment the counter
        $key = md5($this->cacheKeyPrefix.$key);
        $count = $this->memCached->increment($key, $amount);

        // did it work?
        if ($this->memCached->getResultCode() != \Memcached::RES_SUCCESS) {
            return 0;
        }

        return $count;
    }


    /**
     * Flushes all items stored in the cache
     * @return mixed
     */
    public function flush()
    {
        // If we don't have memCached, then fail
        if (!$this->memCached) {
            return;
        }

        // Flush the cache
        $this->memCached->flush();
    }
}
