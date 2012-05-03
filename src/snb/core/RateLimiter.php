<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;
use \snb\core\DatabaseInterface;


//==============================
// RateLimiter
// Support for rate limiting requests
// Once you exceed the rate limit, capped() will return true for the full expire time
//==============================
class RateLimiter
{
	protected $requests;
	protected $maxRequests;
	protected $key;
	protected $expire;



	//==============================
	// Construct
	// goes and increments the counter in memcached and sets
	// a flag once the rate limit has been exceeded
	//==============================
	public function __construct($key, $maxRequests=20, $minutes=5, $autoIncrement=true)
	{
		// snap the max requests value into a reasonable range
		$maxRequests = (int) $maxRequests;
		if ($maxRequests<1)
			$maxRequests = 1;

		// snap the minutes into a reasonable range
		$minutes = (int) $minutes;
		if ($minutes>300)
			$minutes = 300;
		elseif ($minutes<1)
			$minutes = 1;

		// decide when the rate limit will expire (in seconds).
		// this is based on the time delay request, with a random jitter added
		// to make it harder to predict when the key will expire
		// (so it's harder to target requests for the moment the key expires)
		$this->expire = $minutes * 60 + mt_rand(-17, 17);

		// store the data we need in the object
		$this->key = $key;
		$this->maxRequests = $maxRequests;
		$this->requests = 0;

		// increment if needed
		if ($autoIncrement)
			$this->increment();
	}



	//==============================
	// increment
	// Count an event, incrementing the counter
	//==============================
	public function increment()
	{
		// finally, add and increment the count in the cache
		$db = Database::getInstance();
		$requests = $db->incrementCache('rate limit :: '.$this->key, $this->expire);

		// have we go over the rate limit?
		if ($requests > $this->maxRequests)
		{
			// Yep, we have hit our limit, so record the fact that we exceeded the cap
			$db->incrementCache('capped rate limit :: '.$this->key, $this->expire);
		}
	}

	


	//==============================
	// capped
	// Checks to see if this rate limiter has been capped (ie, rate limit was exceeded)
	// returns true if the you should be capped (ie, you have exeeded the rate limit)
	//==============================
	public function capped()
	{
		// find out if we are capped (the key will exist if we are capped, won't exist if we are not capped)
		$db = Database::getInstance();
		$capped = $db->getCache('capped rate limit :: '.$this->key);
		return ($capped != null);
	}




	//==============================
	// getRequests
	// Finds out now many requests have been made to this rate limiter so far
	// within the recent time limit
	//==============================
	public function getRequests()
	{
		return $this->requests;
	}
}