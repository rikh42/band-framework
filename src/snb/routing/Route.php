<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\routing;
use snb\http\Request;



//==============================
// Route
// Describes a single route that maps a url to a controller
// and back again
//==============================
class Route
{
	protected $name;
	protected $options;			// Method and Controller
	protected $placeholders;	// Types for each of the placeholder values in the route
	protected $defaults;		// Default values for items in the route
	protected $regex;			// The regex that will match a url for this route
	protected $vars;
	protected $matchedArgs;
	protected $controllerClass;
	protected $controllerAction;



	/**
	 * @param $name
	 * @param $url
	 * @param array $options
	 * @param array $placeholders
	 * @param array $defaults
	 */
	public function __construct($name, $url, array $options, array $placeholders, array $defaults)
	{
		$this->name = $name;
		$this->url = $url;
		$this->options = $options;
		$this->placeholders = $placeholders;
		$this->defaults = $defaults;
		$this->vars = array();
		$this->matchedArgs = array();
		$this->regex = false;
		$this->controllerClass = null;
		$this->controllerAction = null;
	}


	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getOption('method', 'GET|POST|PUT|DELETE|HEAD');
	}


	/**
	 * @return string - the name of the route
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * returns the protocols that this routes is valid for
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->getOption('protocol', 'http|https');
	}



	/**
	 * Returns the named option of the route
	 * @param $name - the name of the option we want to get
	 * @param null $default
	 * @return mixed
	 */
	public function getOption($name, $default=null)
	{
		// Check that the option exists
		if (array_key_exists($name, $this->options))
			return $this->options[$name];

		return $default;
	}



	/**
	 * isMatch
	 * Trys to match the url of the request to this route
	 * @param string $path - the is the urldecoded path in the url
	 * @param \snb\http\Request $request
	 * @return bool
	 */
	public function isMatch($path, Request $request)
	{
		// See if it is a match for this route
		$regex = $this->getRegex();
		if (!preg_match($regex, $path, $regs))
		{
			return false;
		}

		// OK, this URL is a good one
		// Check that the request method matches the route
		$method = $this->getMethod();
		$m = explode('|', mb_strtoupper($method));
		if (!in_array($request->getMethod(), $m))
		{
			return false;
		}

		// Something similar for the protocol
		$protocol = $this->getProtocol();
		$p = explode('|', mb_strtolower($protocol));
		if (!in_array($request->getProtocol(), $p))
		{
			return false;
		}

		// It is, so prepare the variables and create the controller
		$p = array();
		foreach($this->vars as $key=>$name)
		{
			// copy over the default value if there is one
			if (key_exists($name, $this->defaults))
				$p[$name] = $this->defaults[$name];

			// replace it with the value from url, if it was included
			if (key_exists($key + 1, $regs))
				$p[$name] = $regs[$key + 1];
		}

		// remember these, as someone it likely to want them very soon
		$this->matchedArgs = $p;

		// This route was a match
		return true;
	}


	/**
	 * getController
	 * @return mixed
	 */
	public function getController()
	{
		if ($this->controllerClass==null)
			$this->refreshControllerInfo();

		return $this->controllerClass;
	}


	/**
	 * refreshControllerInfo
	 * Updates the controller class name and action name from the
	 * colon separated text from the yml file
	 */
	protected function refreshControllerInfo()
	{
		if ($this->controllerClass==null)
		{
			$parts = preg_split('/:/', $this->options['controller']);
			$this->controllerClass = $parts[0].'\\controllers\\'.$parts[1];
			$this->controllerAction = $parts[2].'Action';
		}
	}


	/**
	 * Returns the list of matched arguments
	 * @return array
	 */
	public function getArguments()
	{
		return $this->matchedArgs;
	}


	/**
	 * Returns the name of the action on the controller to call
	 * @return string
	 */
	public function getAction()
	{
		if ($this->controllerAction==null)
			$this->refreshControllerInfo();

		return $this->controllerAction;
	}


	/**
	 * generates a full url for this route, given the provided arguments
	 * @param array $args - the list of arguments used to generate the url
	 * @return mixed|string - a url
	 */
	public function generate($args=null)
	{
		// If null is passed in, then using the matching argument list as input
		if ($args==null)
		{
			$args = $this->matchedArgs;
		}

		// If the argument list isn't an array, use an empty array.
		if (!is_array($args))
		{
			$args = array();
		}

		// start with the base path
		$path = $this->url;

		// remove the default values marker
		$path = preg_replace('/::/u', '', $path);

		// generate the regex and find the list of vars in the url
		$this->getRegex();

		// try and add in any variables
		foreach($this->vars as $var)
		{
			// replace [arg] with actual argument value
			// either add the value in, or use the default
			$type = isset($this->placeholders[$var]) ? $this->placeholders[$var] : 'text';
			if (array_key_exists($var, $args))
			{
				$value = $this->cleanArgument($args[$var], $type);
			}
			else
			{
				// no argument provided
				if ($this->defaults[$var] != null)
				{
					// Use the default value
					$value = $this->cleanArgument($this->defaults[$var], $type);
				}
				else
				{
					// or a made up dummy value
					$value = $this->getDummyValue($type);
				}
			}

			$pattern = '/\{'.$var.'\}/u';
			$path = preg_replace($pattern, $value, $path);
		}

		// now look for arguments that have not been used
		$qs = array();
		foreach($args as $name => $value)
		{
			// Is there an entry for this in the vars lists?
			if (!in_array($name, $this->vars))
			{
				// nope, so we need to add it as a query string
				$qs[] = $name .'='. urlencode($value);
			}
		}

		// Finally, add the query string to the url if needed
		if (count($qs) > 0)
		{
			$path .= '?'.implode('&', $qs);
		}

		return $path;
	}



	//==============================
	// getDummyValue
	// gets a dummy value for urls that are missing parameters
	//==============================
	protected function getDummyValue($type)
	{
		if ($type == 'int')
			return 0;

		return 'none';
	}


	//==============================
	// cleanArgument
	// Gets an argument ready for adding to the url
	//==============================
	protected function cleanArgument($value, $type)
	{
		// convert the value to the appropriate type
		switch ($type)
		{
			case 'int':
				$value = (int) $value;
				break;

			default:
				$value = (string) $value;
				break;
		}

		// before url encoding it
		return urlencode($value);
	}


	/**
	 * getRegex
	 * Generates the regular expression that will match this route
	 * @return string
	 */
	public function getRegex()
	{
		// If we have already generated the regex, then just return it
		if ($this->regex)
			return $this->regex;

		// Start building the regex
		$regex = '%^'.$this->url;

		if (preg_match('/::/u', $regex))
		{
			$regex = preg_replace('/::/u', '(?:', $regex);
			$regex .= ')?';
		}

		// Find all the placeholders in the url
		$this->vars = array();
		preg_match_all('/\{([a-z0-9_]+)\}/iu', $regex, $result, PREG_PATTERN_ORDER);
		foreach ($result[1] as $var)
		{
			$type = isset($this->placeholders[$var]) ? $this->placeholders[$var] : 'text';
			$r = $this->getTypeRegex($type);
			$pattern = '/\{'.preg_quote($var).'\}/u';
			$regex = preg_replace($pattern, $r, $regex);
			$this->vars[] = $var;
		}

		// finally, require that we match the entire url
		$regex .= '$%u';

		// remember it for next time
		$this->regex = $regex;
		return $this->regex;
	}




	/**
	 * Convert a type, like alpha, to a suitable regex
	 * Unknown types are assumed to be regex's already
	 * @param $type
	 * @return string
	 */
	protected function getTypeRegex($type)
	{
		switch ($type)
		{
			case 'int':
				$r = '([0-9]+)';
				break;
			case 'slug':
				$r = '([a-zA-Z0-9-]+)';
				break;
			case 'alphanum':
				$r = '([a-zA-Z0-9]+)';
				break;
			case 'alpha':
				$r = '([a-zA-Z]+)';
				break;
			case 'text':
				$r = '([^/]+)';
				break;
			default:	// treat as a regex
				$r = '('.$type.')';
		}

		return $r;
	}
}