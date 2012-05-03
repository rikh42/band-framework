<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;
use snb\core\ContainerAware;
use snb\security\SecurityContext;
use snb\security\SecurityToken;
use snb\security\PasswordHash;
use snb\http\SessionStorage;
use snb\http\Request;
use snb\http\Response;
use snb\http\Cookie;
use snb\events\ResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Notes:
 *
 * Should this perhaps store the cookies it wants to write to the response.
 * When it decides it wants to write a cookie, it has to register for the
 * kernel.response event that lets you modify responses before they are sent...
 */


//==============================
// Auth
// Helps with the process of automatically signing users in from
// the session or from a cookie.
// naturally, there needs to be some validation of this process
//==============================
class Auth extends ContainerAware
{
	protected $identity;
	protected $context;
	protected $cookieJar;


	//==============================
	// __construct
	//==============================
	function __construct(SecurityTokenInterface $token, SecurityContext $context, EventDispatcherInterface $dispatcher)
	{
		$this->identity = $token;
		$this->context = $context;
		$this->cookieJar = array();

		// and register our interest in the kernel response event
		$dispatcher->addListener('kernel.response', array($this, 'addCookiesToResponse'), 100);
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
		return $this->identity->getTokenString();
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
		$session = $this->container->get('session');
		if ($session)
			$session->remove($this->context->name);

		// drop the cookie
		$request = $this->container->get('request');
		if ($request)
		{
			$cookies = $request->cookies;
			$cookies->remove($this->context->name);
		}

		// and prevent it coming back by replacing it with an empty value that has expired
		$empty = '';
		$cookie = new Cookie($this->context->name, $empty, -1, $this->context->path);
		$this->cookieJar[] = $cookie;

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
		// LOG: Auto sign in using passed token worked. Writing data to session
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
		$session = $this->container->get('session');
		if (!$session)
			return false;

		// try and load the session value into the identity
		if (!$this->identity->loadFromValue($session->get($this->context->name, '')))
			return false;

		// check they are still valid
		if (!$this->identity->isActive())
			return false;

		// All ok
		// LOG: Auto sign in using session worked
		return true;
	}




	//==============================
	// signInFromCookie
	// Attempts to sign in using a token found in the cookie
	//==============================
	protected function signInFromCookie()
	{
		// try and get access to the cookies
		$request = $this->container->get('request');
		if (!$request)
			return false;

		$cookies = $request->cookies;
		if (!$cookies)
			return false;

		// Get the token in the cookie, if it exists
		$cookieValue = $cookies->get($this->context->name);
		if (!$this->identity->loadFromValue($cookieValue))
			return false;

		// the cookie looked like a valid value - check the credentials properly
		if (!$this->identity->validate())
			return false;

		// Yes, it is good, so re-issue the updated cookie and put something in the session
		// LOG: Auto sign in using cookie worked. updating cookie and session
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
		// LOG: Auto sign failed to find suitable credentials
		return false;
	}




	/**
	 * Given a username and password, finds out the users password hash,
	 * validates that it is a good match to the password.
	 * If all is well, we generate and store a token that can be used
	 * that can be used to provide access in future requests.
	 * Note that this function will not deal with sessions or cookies though.
	 * See authorise() for that.
	 * @param $username
	 * @param $password
	 * @return bool - true if the username and password are valid
	 */
	public function authoriseSimple($username, $password)
	{
		// default to fail
		$this->identity->reset();
		// LOG:Attempting to authorise user

		// Try and find the user
		$userProvider = $this->container->get('auth.userprovider');
		if (!$userProvider->loadFromUserName($username))
			return false;

		// Check to see if the password is valid
		$pw = new PasswordHash();
		if (!$pw->ValidatePassword($password, $userProvider->getUserHash()))
		{
			// LOG: Failed: bad password
			return false;
		}

		// Pick a lifetime for the token (default to 10 mins)
		$life = 600;
		if ($this->context)
			$life = $this->context->life;

		// I guess it must be good. Generate a new token, in a new series
		$this->identity->generateToken($userProvider->getUserId(), time() + $life);
		// LOG: Worked: regenerated token
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
		/**
		 * @var \snb\http\SessionStorageInterface $session
		 */

		// get the session and write the value into it
		$session = $this->container->get('session');
		if ($session)
			$session->set($this->context->name, $this->identity->getTokenString());
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
					$this->identity->getTokenString(),
					$this->identity->expires,
					$this->context->path);

		// Just store this away for now
		$this->cookieJar[] = $cookie;
	}




	/**
	 * Event Handler for the Response Event. We hook into this to add our
	 * Cookies to the response at the proper time
	 * @param \snb\events\ResponseEvent $event
	 * @return null
	 */
	public function addCookiesToResponse(ResponseEvent $event)
	{
		// do nothing if there are no cookies here
		if (count($this->cookieJar)==0)
			return;

		// Get access to the response
		$response = $event->getResponse();

		// Add all the waiting cookies to it...
		foreach($this->cookieJar as $cookie)
		{
			$response->addCookie($cookie);
		}

		// empty out the jar
		$this->cookieJar = array();
	}

}
