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
 * Handles session storage
 */
class SessionStorage implements SessionStorageInterface
{
	protected $started;



	//==============================
	// __construct
	//==============================
	public function __construct()
	{
		// default values
		$this->started = false;
	}



	//==============================
	// start
	// Starts a session
	//==============================
	public function start()
	{
		// If we have already started the session, don't do it again
		if ($this->started)
			return;

		// Set the name of the session to something other than PHPSESSION
		//session_name('snb');
		session_start();
		$this->started = true;
	}



	//==============================
	// get
	// Gets a value stored in the session
	//==============================
	public function get($key, $default=null)
	{
		if (array_key_exists($key, $_SESSION))
		{
			return $_SESSION[$key];
		}

		return $default;
	}



	//==============================
	// set
	// Sets a value to store it in the session
	//==============================
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}



	//==============================
	// remove
	// removes an item from the session
	//==============================
	public function remove($key)
	{
		if (array_key_exists($key, $_SESSION))
		{
			unset($_SESSION[$key]);
		}
	}

}
