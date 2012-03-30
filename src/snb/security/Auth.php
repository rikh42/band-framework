<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;

use \snb\security\SecurityContext;
use \snb\security\SecurityToken;
use \snb\security\PasswordHash;
use \snb\core\Controller;
use \snb\core\SessionStorage;
use \snb\http\Request;
use \snb\http\Response;
use \snb\core\Database;
use \snb\http\Cookie;



//==============================
// Auth
// Helps with the process of automatically signing users in from
// the session or from a cookie.
// naturally, there needs to be some validation of this process
//==============================
class Auth
{
	protected $request;
	protected $response;
	protected $identity;
	protected $context;




	//==============================
	// __construct
	//==============================
	function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
		$this->identity = new SecurityToken();

		// Could we work out a suitable context from the request ourselves, in here
		$this->context = null;
	}




	//==============================
	// setSecurityContext
	// Sets the context for the area of the site we are in
	//==============================
	public function setSecurityContext(SecurityContext $context)
	{
		$this->context = $context;
	}





	//==============================
	// hasIdentity
	// return true if we have a user id in the system
	//==============================
	public function hasIdentity()
	{
		return ($this->identity->userId != 0);
	}




	//==============================
	// getIdentity
	// Gets the user id
	//==============================
	public function getIdentity()
	{
		return $this->identity->userId;
	}




	//==============================
	// getToken
	// Get the security token for the identity in place
	//==============================
	public function getToken()
	{
		return $this->identity->getValue();
	}


	//==============================
	// isHacked
	// If we detect that a sign in cookie has been hacked, this returns true
	// we should warn the user about this state - all cookies and sessions will
	// have already been killed when this returns true
	//==============================
	public function isHacked()
	{
		return ($this->identity->state == SecurityToken::HACKED);
	}





	//==============================
	// signOut
	// Drops the session data, sign in cookie and all the active session in the database
	//==============================
	public function signOut()
	{
		// drop the session
		$session = $this->request->getSession();
		if ($session)
			$session->remove($this->context->name);

		// drop the cookie
		$cookies = $this->request->cookies;
		$cookies->remove($this->context->name);

		// and prevent it coming back by replacing it with an empty value that has expired
		$inThePast = time() - $this->context->life;
		$empty = '';
		$cookie = new Cookie($this->context->name, $empty, $inThePast, $this->context->path);
		$this->response->addCookie($cookie);

		// If they log out, should that log them out everywhere?
		// I think so.
		$this->identity->clearAll();

		// clear the identity
		$this->identity->reset();
	}




	//==============================
	// signInFromToken
	// Attempts to sign in using a token passed in
	//==============================
	protected function signInFromToken($token)
	{
		// Check we actually have a token
		if (empty($token))
			return false;

		// Load the token into the identity
		if (!$this->identity->loadFromValue($token))
			return false;

		// Check it is still valid
		if (!$this->identity->validate())
			return false;

		// and put something into the session
		\Logger::log('Auto sign in using passed token worked. Writing data to session');
		$this->writeSession();
		return true;
	}





	//==============================
	// signInFromSession
	// Attempts to sign in using a token found in the session
	//==============================
	protected function signInFromSession()
	{
		// load in the token values from the session
		$session = $this->request->getSession();
		if ((!$session) || (!$this->context))
			return false;

		// try and load the session value into the identity
		if (!$this->identity->loadFromValue($session->get($this->context->name, '')))
			return false;

		// check they are still valid
		if (!$this->identity->isActive())
			return false;

		// All ok
		\Logger::log('Auto sign in using session worked.');
		return true;
	}




	//==============================
	// signInFromCookie
	// Attempts to sign in using a token found in the cookie
	//==============================
	protected function signInFromCookie()
	{
		// try and get access to the cookies
		$cookies = $this->request->cookies;
		if (!$cookies)
			return false;

		// make sure we have a valid context
		if (!$this->context)
			return false;

		// Get the token in the cookie, if it exists
		$cookieValue = $cookies->get($this->context->name);
		if (!$this->identity->loadFromValue($cookieValue))
			return false;

		// the cookie looked like a valid value - check the credentials properly
		if (!$this->identity->validate())
			return false;

		// Yes, it is good, so re-issue the updated cookie and put something in the session
		\Logger::log('Auto sign in using cookie worked. updating cookie and session');
		$this->writeCookie();
		$this->writeSession();
		return true;
	}



	//==============================
	// autoSignIn
	// Attempts to automatically sign in using data from the session.
	// If there is nothing usable in the session, we also look for a remember me cookie
	// and try and sign in from that.
	// If it worked, hasIdentity() will return true
	//==============================
	public function autoSignIn($token=null)
	{
		// reset the identity now
		$this->identity->reset();

		// load from the token passed in?
		if ($this->signInFromToken($token))
			return true;

		// load from the session
		if ($this->signInFromSession())
			return true;

		// load from the session
		if ($this->signInFromCookie())
			return true;

		// failed to find any valid source
		\Logger::log('Auto sign failed to find suitable credentials');
		return false;
	}




	//==============================
	// authoriseSimple
	// Generates a new security token for a new user in a new account.
	// Does nothing with cookies or sessions, as both of those can not be used to map the
	// token into the new account (due to a domain name change at this same point).
	//==============================
	public function authoriseSimple($username, $password)
	{
		// default to fail
		$this->identity->reset();
		\Logger::log('Attempting to authorise user');

		// Try and find the user
		$db = Database::getInstance();
		$sql = "SELECT ixUser, sHash FROM userinfo WHERE sUserName=:username";
		$params = array('text:username'=> $username);
		$user = $db->row($sql, $params);

		// if there was no user, fail
		if (!$user)
		{
			\Logger::log('Failed: no user');
			return false;
		}

		// Check to see if the password is valid
		$pw = new PasswordHash();
		if (!$pw->ValidatePassword($password, $user->sHash))
		{
			\Logger::log('Failed: bad password');
			return false;
		}

		// Pick a lifetime for the token
		$life = 600;
		if ($this->context)
			$life = $this->context->life;

		// I guess it must be good. Generate a new token, in a new series
		$this->identity->generateToken($user->ixUser, time() + $life);
		\Logger::log('Worked: regenerated token');
		return true;
	}




	//==============================
	// authorise
	// Attempts to sign in using credentials from a sign in form.
	// If the credentials are ok, the session will be set up and possibly a remember me cookie
	// true if they signed in ok, false if not.
	//==============================
	public function authorise($username, $password, $rememberMe=false)
	{
		// attempt to authorise the user
		if (!$this->authoriseSimple($username, $password))
			return false;

		// Worked, so keep them signed in, in the session
		$this->writeSession();

		// If remember me is on, then try and create a cookie for later
		if ($rememberMe)
			$this->writeCookie();

		return true;
	}




	//==============================
	// writeSession
	// Writes the appropriate values to the session, ready for next time
	//==============================
	protected function writeSession()
	{
		// get the session and write the value into it
		$session = $this->request->getSession();
		if ($session)
			$session->set($this->context->name, $this->identity->getValue());
	}




	//==============================
	// writeCookie
	// Writes the appropriate values to a cookie, ready for tomorrow
	//==============================
	protected function writeCookie()
	{
		// Create a cookie to send off
		$cookie = new Cookie(
					$this->context->name,
					$this->identity->getValue(),
					$this->identity->expires,
					$this->context->path);

		// then we want to add this to the response
		$this->response->addCookie($cookie);
	}

}
