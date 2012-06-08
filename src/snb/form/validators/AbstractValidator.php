<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\form\validators;

use snb\form\validators\ValidatorInterface;




/**
 * Validate that the field is Not Blank
 */
class AbstractValidator implements ValidatorInterface
{
	protected $messages;
	protected $errors;


	/**
	 * clear the message
	 * @param null $options
	 */
		public function __construct($options=null)
	{
		$this->messages = array();
		$this->clearErrors();
	}



	/**
	 * Sets the error message for the validator
	 * @param string $name
	 * @param $msg
	 */
	public function setMessage($name, $msg)
	{
		$this->messages[$name] = $msg;
	}



	/**
	 * Get the array of errors for this validator
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}



	/**
	 * Clear all the errors (useful if reusing a validator)
	 * isValid should generally start by calling this to be sure.
	 */
	public function clearErrors()
	{
		$this->errors = array();
	}


	/**
	 * base class will just say "yes, its valid"
	 * @param $value
	 * @return bool
	 */
	public function isValid($value)
	{
		// what the hell, lets say yes
		$this->clearErrors();
		return true;
	}



	/**
	 * Adds the named message as an error.
	 * @param string $name - the name of the message
	 * @param array $args - the list of arguments to replace in the message
	 */
	protected function addError($name, array $args=array())
	{
		if (array_key_exists($name, $this->messages))
		{
			$this->errors[] = $this->createMessage($this->messages[$name], $args);
		}
	}



	/**
	 * Remaps all the {{value}} settings in the error message to contain the value
	 * @param $msg
	 * @param $args
	 * @return mixed
	 */
	protected function createMessage($msg, $args)
	{
		// replace all the named values in the string
		foreach($args as $key=>$value)
		{
			$replace = '{{'.$key.'}}';
			$value = is_object($value) ? 'object' : is_array($value) ? 'array' : (string)$value;
			$msg = str_replace($replace, $value, $msg);
		}

		return $msg;
	}
}
