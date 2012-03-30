<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;

use \snb\core\Database;
use \snb\security\PasswordHash;



//==============================
// SecurityToken
// The token we use to authenticate returning visitors
//==============================
class SecurityToken
{
	const FAIL = 0;
	const MATCH = 1;
	const HACKED = 2;


	public $userId;
	public $series;
	public $token;
	public $expires;
	public $state;

	protected $db;
	protected $passwordHash;



	//==============================
	// __construct
	//==============================
	public function __construct()
	{
		$this->reset();
		$this->db = Database::getInstance();
		$this->passwordHash = new PasswordHash();
	}



	//==============================
	// reset
	// clears everything down to default bad values
	//==============================
	public function reset()
	{
		$this->userId = 0;
		$this->series = '';
		$this->token = '';
		$this->state = SecurityToken::FAIL;

		// Time when the token expires, as unix timestamp
		// Zero has special meaning in cookies, so use 1 second after 1970.
		$this->expires = 1;
	}



	//==============================
	// generateToken
	// Fills the token with fresh values (new series and new token)
	// and links it to the user specified. The token is stored in the database
	//==============================
	public function generateToken($userId, $expires)
	{
		// create some random noise
		$this->userId = $userId;
		$this->series = $this->passwordHash->generateRandomToken(16);
		$this->token = $this->passwordHash->generateRandomToken(16);
		$this->expires = $expires;
		$this->state = SecurityToken::MATCH;

		// purge expired tokens from the database
		$sql = "DELETE from tokens WHERE iExpires>:now";
		$this->db->query($sql, array('int:now'=>time()));

		// store the new token in the database
		$sql = "INSERT into tokens (ixToken, iUser, sSeries, sToken, iExpires) VALUES (NULL, :user, :series, :token, :expires)";
		$params['int:user'] = $this->userId;
		$params['text:series'] = $this->series;
		$params['text:token'] = $this->token;
		$params['int:expires'] = $expires;

		if ($this->db->query($sql, $params) != 1)
		{
			// failed to add the token to the db - bin it
			$this->reset();
		}
	}






	//==============================
	// isActive
	// Determines if the token is still active and in the database
	// ie, it has not expired, and has not been revoked.
	//==============================
	public function isActive()
	{
		if ($this->userId == 0)
			return false;

		// Check that this token is still in the database and has not expired
		$sql = "SELECT ixToken FROM tokens WHERE iUser=:user AND sSeries=:series AND sToken=:token AND iExpires > :expires";
		$params = array(
			'int:user' => $this->userId,
			'text:series' => $this->series,
			'text:token' => $this->token,
			'int:expires' => time()
			);

		// try and find this token in the database
		$ixToken = $this->db->one($sql, $params);
		if (!$ixToken)
		{
			$this->reset();
			return false;
		}

		// yay!
		$this->state = SecurityToken::MATCH;
		return true;
	}




	//==============================
	// validate
	// Validates the token against the database.
	// if the token, user and series all match, then the token is updated in the database (with a new one)
	// using the same series as before. The function then returns true
	// If the user and series match, but the token does not, then it indicates that the account has been compromised.
	// All tokens for the user and removed, preventing any more cookie based logins. It also means that all
	// logged in instances of this user will be immediately logged out. returns false.
	// If the user and series can not be found, then we clear the token and return false;
	//==============================
	public function validate()
	{
		if ($this->userId==0)
			return false;

		// Finds the token with matching user and series
		$sql = "SELECT * FROM tokens WHERE iUser=:user AND sSeries=:series AND iExpires > :expires";
		$params = array(
			'int:user' => $this->userId,
			'text:series' => $this->series,
			'int:expires' => time()
			);

		$res = $this->db->row($sql, $params);
		if ($res)
		{
			// Yes, we found a token from our series. Check we have the current one
			if ($this->token == $res->sToken)
			{
				// Yes, this is a full match
				// This token needs to be updated, so regenerate the token
				$this->token = $this->passwordHash->generateRandomToken(16);
				$this->expires = $res->iExpires;
				$this->state = SecurityToken::MATCH;

				// and update the database
				$sql = "UPDATE tokens SET sToken=:token WHERE ixToken=:tokenid";
				$this->db->query($sql, array('text:token'=>$this->token, 'int:tokenid'=>$res->ixToken));
				return true;
			}

			// token is not a match - we are being hacked.
			// Log everyone out
			$this->clearAll();
			$this->state = SecurityToken::HACKED;
			return false;
		}

		// dump all the values
		$this->reset();
		return false;
	}





	//==============================
	// clearAll
	// Removes all tokens in the database connected with the user for this token
	//==============================
	public function clearAll()
	{
		// nothing to do when there is no user
		if ($this->userId == 0)
			return;

		// drop all tokens associated with this user
		$sql = "DELETE from tokens WHERE iUser=:user";
		$this->db->query($sql, array('int:user'=>$this->userId));

		// reset the token
		$this->reset();
	}




	//==============================
	// getValue
	// gets the value of the token as a string, ready for insertion into the session or cookie
	//==============================
	public function getValue()
	{
		return "$this->userId:$this->series:$this->token";
	}




	//==============================
	// loadFromValue
	// Sets up the token using the data in the string (typically from a cookie or session
	// that got its data from getValue())
	//==============================
	public function loadFromValue($value)
	{
		// clear before we start
		$this->reset();

		// Check that the cookie value is in the correct format. All bogus cookies will be ignored
		if (!preg_match('%^([0-9]+):([a-zA-Z0-9./]{16}):([a-zA-Z0-9./]{16})$%', $value, $parts))
			return false;

		// get the extracted parts and store them away
		$this->userId = (int)$parts[1];
		$this->series = $parts[2];
		$this->token = $parts[3];
		$this->state = SecurityToken::FAIL;		// assume its bad for now
		return true;
	}
}



