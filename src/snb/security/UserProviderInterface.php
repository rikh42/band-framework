<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\security;


/**
 * This interface is used by the authentication system to provide a method
 * of loading certain user information and making it available to the system.
 * Typically classes would exist that know how to read the data from a database
 * or config system.
 */
interface UserProviderInterface
{
	/**
	 * This is called to load the data, using the username
	 * on most systems the username would be an email address, or unique name for the user
	 * @abstract
	 * @param $username
	 * @return bool - true if load was successful, false if it failed
	 */
	function loadFromUserName($username);


	/**
	 * Returns an int that is unique to the user
	 * @abstract
	 * @return int
	 */
	function getUserId();


	/**
	 * Returns the user name (normally would be the same as passed to loadFromUserName)
	 * @abstract
	 * @return string
	 */
	function getUserName();


	/**
	 * Returns the password hash associated with the user
	 * @abstract
	 * @return string
	 */
	function getUserHash();
}