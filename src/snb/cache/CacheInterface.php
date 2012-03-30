<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\cache;

interface CacheInterface
{
	public function get($key);
	public function remove($key);
	public function set($key, $value, $expire = 60);
	public function increment( $key, $amount=1);
}