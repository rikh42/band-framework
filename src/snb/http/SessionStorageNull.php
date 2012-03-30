<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;

use snb\http\SessionStorageInterface;



/**
 * A null session handler - basically, does not start a session, and nothing you try and store
 * in it will stick. reading from it will always return the default provided.
 */
class SessionStorageNull implements SessionStorageInterface
{

	public function start()
	{
	}

	public function get($key, $default=null)
	{
		return $default;
	}

	public function set($key, $value)
	{
	}

	public function remove($key)
	{
	}
}
