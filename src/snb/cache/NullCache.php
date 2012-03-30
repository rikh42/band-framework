<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\cache;
use snb\cache\CacheInterface;




/**
 * NullCache
 * This is a dummy cache class that can be used in debug
 * builds to effectively disable the cache
 */
class NullCache implements CacheInterface
{

	/**
	 * @param $key
	 * @return null
	 */
	public function get($key)
	{
		return null;
	}


	/**
	 * @param $key
	 */
	public function remove($key)
	{
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expire
	 */
	public function set($key, $value, $expire=60)
	{
	}


	/**
	 * @param $key
	 * @param int $expire
	 */
	public function increment($key, $amount=1)
	{
	}
}
