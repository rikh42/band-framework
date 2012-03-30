<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;


//==============================
// Request
// Wraps up all the information about the current http request
//==============================
class RequestParams
{
	protected $all;

	public function __construct($all = array())
	{
		$this->all = $all;
	}


	//==============================
	// all
	// returns all the parameters
	//==============================
	public function all()
	{
		return $this->all;
	}



	//==============================
	// addItem
	// Adds a single extra item to the list
	// replacing any value of the same name
	//==============================
	public function addItem($name, $value)
	{
		$this->all[$name] = $value;
	}


	//==============================
	// addMany
	// Adds an array of new name value pairs to the params
	// replacing any value of the same name
	//==============================
	public function addMany($extras)
	{
		$this->all = array_replace($this->all, $extras);
	}

	//==============================
	// has
	// returns true if the named items exists in the request params
	//==============================
	public function has($name)
	{
		return array_key_exists($name, $this->all);
	}



	//==============================
	// remove
	// removes an entry from the request params
	//==============================
	public function remove($name)
	{
		// is it in there?
		if (!$this->has($name))
			return;

		// yep, so remove it.
		unset($this->all[$name]);
	}


	//==============================
	// count
	// returns the number of items in the request
	//==============================
	public function count()
	{
		return count($this->all);
	}


	//==============================
	// getSignatureString
	// Builds a string that is often used in building signatures for actions
	//==============================
	public function getSignatureString()
	{
		// start a new signature and check for empty argument lists
		$sig = '';
		if ($this->count() == 0)
			return $sig;

		// Sort the arguments alphabetically
		ksort($this->all);

		// build the signature
		foreach($this->all as $name=>$value)
		{
			$sig .= $name.':'.$value."\n";
		}

		// done
		return $sig;
	}


	//==============================
	// get
	// Gets the raw value back from for a given name. or the default value
	//==============================
	public function get($name, $default='')
	{
		if (!array_key_exists($name, $this->all))
		{
			return $default;
		}

		return $this->clean($this->all[$name]);
	}


	//==============================
	// getInt
	// Gets an int for the named value
	//==============================
	public function getInt($name, $default=0)
	{
		return (int) $this->get($name, $default);
	}


	//==============================
	// getText
	// Gets a cleaned text value for the named value, or the default
	//==============================
	public function getText($name, $default='')
	{
		return $this->get($name, $default);
	}




	//==============================
	// getAlpha
	// Gets just the letters from the value (can include accents etc)
	//==============================
	public function getAlpha($name, $default='')
	{
		// basically, strip everything that isn't a letter from the string
		return preg_replace('/[^[:alpha:]]/', '', $this->get($name, $default));
	}



	//==============================
	// getAlphaNum
	// Gets just the letters and digits from the value (can include accents etc)
	//==============================
	public function getAlphaNum($name, $default='')
	{
		return preg_replace('/[^[:alnum:]]/', '', $this->get($name, $default));
	}


	//==============================
	// getAlphaNum
	// Gets just the letters and digits from the value (can include accents etc)
	//==============================
	public function getDigits($name, $default='')
	{
		return preg_replace('/[^[:digit:]]/', '', $this->get($name, $default));
	}



	//==============================
	// clean
	// Clean the value to remove any malformed utf-8 characters
	//==============================
	private function clean($s)
	{
		if (is_string($s) && !mb_check_encoding($s, 'UTF-8'))
		{
			$s = iconv('UTF-8', 'UTF-8//IGNORE', $s);
		}
		return $s;
	}
}
