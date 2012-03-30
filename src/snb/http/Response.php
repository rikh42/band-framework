<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;

use \snb\http\Request;
use \snb\routing\Route;
use \snb\http\Cookie;

use \DateTime;
use \DateTimeZone;


/*
 * Example

HTTP/1.1 200 OK
Date: Thu, 10 Nov 2011 12:05:50 GMT
Server: Apache
X-Powered-By: PHP/5.3.5-1ubuntu7.3
Vary: Accept-Encoding
Content-Encoding: gzip
Content-Length: 545
Keep-Alive: timeout=5, max=100
Connection: Keep-Alive
Content-Type: text/html; charset=UTF-8

 */


//==============================
// Response
// Holds the data for an http response.
//==============================
class Response
{
	const NEVER_CACHE = 1;
	const PRIVATE_CACHE = 2;
	const PUBLIC_CACHE = 3;
	const STATIC_CACHE = 4;

	protected $headers = array();
	protected $cookies = array();
	protected $content;
	protected $responseCode;
	protected $maxAge = 0;

	// List taken from http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	// this isn't a complete list, but it covers way more than we need
	// for each code, it gives up the official name that goes with it.
	protected $httpResponseCodeText = array(
		// 1xx Informational
		100 => 'Continue',
		101 => 'Switching Protocols',

		// 2xx Success
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// 3xx Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		// 4xx Client Error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',				// yes, this really is in the spec!

		// 5xx Server Error
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);


	//==============================
	// __construct
	//==============================
	function __construct($content = '', $responseCode = 200)
	{
		// Set up some standard items - you can remove them if you like...
		//$this->setHeader('X-Powered-By', 'Small Neat Box');
		//$this->setHeader('X-Frame-Options', 'SAMEORIGIN');
		//$this->setHeader('X-XSS-Protection', '1; mode=block');
		//$this->setHeader('X-Content-Type-Options', 'nosniff');
		$this->responseCode = $responseCode;
		$this->setContent($content);
		$this->setContentTypeSimple('html');
	}





	//==============================
	// setDateHeader
	// Sets a time/date based header, making sure that the date is correctly formatted
	//==============================
	protected function setDateHeader($name, DateTime $date)
	{
		// Set the time to UTC, and format correctly
		$date->setTimezone(new DateTimeZone('UTC'));
		$value = $date->format('D, d M Y H:i:s')." GMT";
		$this->setHeader($name, $value);
	}



	//==============================
	// setMaxAge
	// Sets the max time you can cache something for (in seconds)
	//==============================
	public function setMaxAge($age)
	{
		// negative values are not permitted
		$age = (int) $age;
		if ($age<0)
			$age = 0;

		// Don't allow anything over a year, as http1.1 says it shouldn't be more than a year
		$year = 60 * 60 * 24 * 365;
		if ($age > $year)
			$age = $year;

		// sets max-age, s-maxage and expires
		$this->maxAge = (int) $age;
	}





	//==============================
	// buildCacheControlHeader
	// Builds the values for the cacheControl header, based on other settings
	//==============================
	protected function buildCacheControlHeader($isPrivate)
	{
		return 'max-age=' . $this->maxAge;
		/*
		$cacheControl = array();
		if ($isPrivate)
			$cacheControl[] = 'private';
		else
			$cacheControl[] = 'public';

		if ($this->maxAge > 0)
		{
			$cacheControl[] = 'max-age='.$this->maxAge;
			if (!$isPrivate)
				$cacheControl[] = 's-maxage='.$this->maxAge;
		}

		// If the last modified or etag are set, then ask them to revalidate
		if (($isPrivate) || ($this->hasHeader('Last-Modified')) || ($this->hasHeader('ETag')))
		{
			$cacheControl[] = 'must-revalidate';
		}

		// return the expanded result
		return implode(', ', $cacheControl);
		*/
	}



	//==============================
	// setCacheMethod
	// Sets the response to exhibit one of a
	// pre-defined set of caching policies. This is intended to greatly simplify
	// the set up and use of cache control headers within the framework
	//==============================
	public function setCacheMethod($method)
	{
		switch ($method)
		{
			case Response::NEVER_CACHE:
				// no caching at all please
				// set the cache-control header to indicate this, and set an expires date in the past
				$this->setHeader("Cache-Control", "no-cache, no-store, private, must-revalidate");
				$this->setDateHeader("Expires", new DateTime('11th feb 2010 12:42:42'));
				$this->removeHeader('ETag');
				$this->removeHeader('Last-Modified');
				break;

			case Response::PRIVATE_CACHE:
			case Response::PUBLIC_CACHE:
				// cache, but check back
				if ($this->maxAge>0)
				{
					$this->setDateHeader("Expires", new DateTime("+$this->maxAge seconds"));
				}
				$this->setHeader('Vary', 'Accept-Encoding');
				$this->setHeader("Cache-Control", $this->buildCacheControlHeader($method==Response::PRIVATE_CACHE));
				break;

			case Response::STATIC_CACHE:
				// cache forever please.
				$this->setMaxAge(60 * 60 * 24 * 365);
				//$this->setDateHeader("Expires", new DateTime("+365 days"));
				//$this->setHeader('Vary', 'Accept-Encoding');
				$this->setHeader("Cache-Control", $this->buildCacheControlHeader(false));
				break;
		}
	}





	//==============================
	// setETag
	// If you are using some kind of caching (ie not NEVER_CACHE)
	// then it is worth generating an etag for your content.
	// Pass in a block of text that uniquely represents your content
	// and this function will generate a suitable etag record.
	// Important: This should be the same for the same content.
	//==============================
	public function setETag($sampleText)
	{
		$etag = '"'.md5((string)$sampleText).'"';
		$this->setHeader("ETag", $etag);
	}






	//==============================
	// setLastModified
	// Set the time and date when the content was last modified.
	// You should always attempt to set this when the data is known.
	// if you are able to set it early in the process, before much work is done
	// try calling isNotModified()
	//==============================
	public function setLastModified(DateTime $date)
	{
		$this->setDateHeader("Last-Modified", $date);
	}




	//==============================
	// isNotModified
	// Attempts to determine if anything in response has changed since
	// the page was last requested. Compares things like Last-Modified and eTag
	// against the data in the Request.
	// If it looks like nothing has changed, this changes the response to be a
	// 304 Not Modified response
	//==============================
	public function isNotModified(Request $request)
	{
		// gain access to the headers
		$headers = $request->headers;

		// assume that we have been modified, unless we can proove otherwise
		$modified = true;

		// See if there is any Last Modified data in the request
		$lastMod = $headers->get('If-Modified-Since');
		$etag = $headers->get('If-None-Match');

		// If we got both, make sure they are both ok
		if ((!empty($etag)) && (!empty($lastMod)))
		{
			// we have an etag and a last mod date. Ensure they are both a match
			if (($etag == $this->getHeader('eTag')) && ($lastMod == $this->getHeader('Last-Modified')))
				$modified = false;
		}
		elseif (!empty($etag))
		{
			// We only got an etag, so check to see if that matches
			if ($etag == $this->getHeader('eTag'))
				$modified = false;
		}
		elseif (!empty($lastMod))
		{
			// We only got a Last Modified date, so check to see if that matches
			if ($lastMod == $this->getHeader('Last-Modified'))
				$modified = false;
		}

		// Has the document been modified since last time then?
		if (!$modified)
		{
			// It's not been modified, so set up the response to show that
			$this->setNotModified();
		}

		// return true if the document has not been modified...
		return !$modified;
	}




	//==============================
	// setNotModified
	// Called to force the response to be a "Not Modified" response
	//==============================
	public function setNotModified()
	{
		// Set this up to be a Not Modified response
		$this->setHTTPResponseCode(304);

		// 304 should have no content
		$this->setContent('');

		// as there is no content, we should also remove all the headers that are connected to the content
		$this->removeHeader('Content-Length');
		$this->removeHeader('Content-Type');
		$this->removeHeader('Last-Modified');
		$this->removeHeader('Content-Encoding');
		$this->removeHeader('Content-Language');
		$this->removeHeader('Allow');
	}



	//==============================
	// setHTTPResponseCode
	// set the response code to the value given. By default this is set to 200
	//==============================
	public function setHTTPResponseCode($code)
	{
		// stop the code from being crazy
		// code must be an int to start with
		if (!is_int($code))
			return;

		// ensure the code is one of the ones we support
		if (!array_key_exists($code, $this->httpResponseCodeText))
			return;

		// all out of excuses for rejecting it, so set it up
		$this->responseCode = $code;
	}



	//==============================
	// setContentTypeSimple
	// Sets the content type and character set, in a simplified way
	//==============================
	public function setContentTypeSimple($type, $charset='UTF-8')
	{
		// force to lower case
		$type = mb_strtolower($type);

		// see if it is a type we like..
		$contentType = false;
		switch ($type)
		{
			case 'html':
				$contentType = 'text/html; charset='.$charset;
				break;

			case 'json':
				$contentType = 'application/json; charset='.$charset;
				break;

			case 'css':
				$contentType = 'text/css; charset='.$charset;
				break;

			case 'javascript':
				$contentType = 'application/javascript; charset='.$charset;
				break;

			case 'xml':
				$contentType = 'text/xml; charset='.$charset;
				break;

			case 'jpeg':
			case 'jpg':
				$contentType = 'image/jpeg';
				break;

			case 'png':
				$contentType = 'image/png';
		}

		// set the mimetype if we found one
		if ($contentType)
			$this->setHeader('Content-Type', $contentType);
	}


	//==============================
	// setHeader
	// Sets the named header to the value given
	//==============================
	public function setHeader($name, $value)
	{
		$name = $this->getCleanHeaderName($name);
		$this->headers[$name] = $value;
	}


	//==============================
	// hasHeader
	// Determine if the named header has been set before
	//==============================
	public function hasHeader($name)
	{
		$name = $this->getCleanHeaderName($name);
		return array_key_exists($name, $this->headers);
	}

	//==============================
	// getHeader
	// Gets the current value of the named header (or the empty string if it's not been set)
	//==============================
	public function getHeader($name)
	{
		if ($this->hasHeader($name))
		{
			$name = $this->getCleanHeaderName($name);
			return $this->headers[$name];
		}

		return '';
	}

	//==============================
	// removeHeader
	// Removes a header that has already been set
	//==============================
	public function removeHeader($name)
	{
		$name = $this->getCleanHeaderName($name);
		if (array_key_exists($name, $this->headers))
		{
			unset($this->headers[$name]);
		}
	}



	//==============================
	// addCookie
	// Adds a cookie to the response
	//==============================
	public function addCookie(Cookie $cookie)
	{
		$this->cookies[$cookie->name] = $cookie;
	}



	//==============================
	// removeCookie
	// removes a cookie from the list
	//==============================
	public function removeCookie($name)
	{
		unset($this->cookies[$name]);
	}



	//==============================
	// setContent
	// Sets the page content to the text given
	//==============================
	public function setContent($content)
	{
		$this->content = (string) $content;
	}


	//==============================
	// setContentJson
	// Sets the page content to a json encoded version of the object supplied
	//==============================
	public function setContentJson($object)
	{
		$this->content = json_encode($object);
	}


	//==============================
	// setRedirectToRoute
	// sets up the response object to be a page redirect
	// (but does not perform the redirect - that happens when the response is sent by send())
	//==============================
	public function setRedirectToRoute(Route $route, $args = array(), Request $context=null)
	{
		// set up the response to redirect to a named route
		if (!$route)
			return;

		// generate the url for the route
		$url = $route->generate($args);

		// add the domain if we can
		if ($context)
		{
			$url = $context->getHttpHost() . $url;
		}

		// Finally, set the redirect headers
		$this->setRedirectToURL($url);
	}




	//==============================
	// setRedirectToURL
	// Another redirect setup that goes to the specific URL given
	// This should be a full URL, starting http etc
	//==============================
	public function setRedirectToURL($url)
	{
		// set up the response to redirect to a named route
		// url should always be a full url (http.....)
		//header( 'Cache-Control: no-store, no-cache, must-revalidate');
		//header( 'Pragma: no-cache');
		//header( 'Location: '.$url );

		$this->setHTTPResponseCode(302);
		$this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		$this->setHeader('Location', $url);
	}




	//==============================
	// noRouteFound
	// Called when no route was found by the front controller
	// We set up the response to be a 404 page
	//==============================
	public function noRouteFound(Request $request)
	{
		$this->setHTTPResponseCode(404);
	}


	//==============================
	// getCleanHeaderName
	// Helper function that provides a consistent formatting for header names
	//==============================
	protected function getCleanHeaderName($name)
	{
		// header names should really be capitalised, with hypens between words
		// eg X-Powered-By and not x_powered_by
		// this function helps enforce that

		// convert any likely looking separators to a space
		$clean = str_replace(array('_', '-'), ' ', (string)$name);

		// capitalise each word
		$clean = mb_convert_case($clean, MB_CASE_TITLE);

		// finally, turn all the spaces back into '-'
		return str_replace(' ', '-', $clean);
	}




	//==============================
	// sendHeaders
	// Helper function that sends all the page headers
	//==============================
	protected function sendHeaders()
	{
		// set the response code
		$status = 'HTTP/1.1 '.$this->responseCode.' '.$this->httpResponseCodeText[$this->responseCode];
		header($status);

		// followed by all the other headers
		foreach($this->headers as $name => $value)
		{
			$text = $name;
			$text .= ': ';
			$text .= $value;
			header($text);
		}

		// Send all the cookies as well, if there are any
		foreach ($this->cookies as $cookie)
		{
			$cookie->setCookie();
		}
	}



	//==============================
	// send
	// Sends the page to the browser
	//==============================
	public function send()
	{
		$this->sendHeaders();
		echo $this->content;
	}




	//==============================
	// sendAndExit
	// Sends the response right now, and then terminates the script
	//==============================
	public function sendAndExit()
	{
		$this->send();
		exit();
	}
}
