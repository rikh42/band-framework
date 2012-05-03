<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;



interface SecurityTokenInterface
{
	/**
	 * resets the token to bad values (ie, no you can't log in)
	 */
	function reset();


	/**
	 * Fills the token with fresh values (new series and new token)
	 * and links it to the user specified. The token is stored in the database
	 * @param $userId
	 * @param $expires
	 */
	function generateToken($userId, $expires);


	/**
	 * Determines if the token is still active and in the database
	 * ie, it has not expired, and has not been revoked.
	 * @return bool
	 */
	function isActive();

	/**
	 * @abstract
	 * Validates the token
	 */
	function validate();


	/**
	 * @abstract
	 *
	 */
	function clearAll();


	/**
	 * @abstract
	 * gets the value of the token as a string
	 */
	function getTokenString();


	/**
	 * Sets up the token using the data in the string (typically from a cookie or session
	 * that got its data from getToken())
	 * @abstract
	 * @param $value
	 */
	function loadFromValue($value);
}



