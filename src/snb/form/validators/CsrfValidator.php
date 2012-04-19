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
 * Validate that the csrf field has a valid token in it
 */
class CsrfValidator extends AbstractValidator
{
	const MsgInvalidToken = 'invalidToken';
	protected $token;
	protected $logger;

	/**
	 * @param null $options - expects to find a ['token'] entry in
	 * options, with the token in it
	 */
	public function __construct($options = null)
	{
		parent::__construct($options);
		$this->setMessage(self::MsgInvalidToken, 'Unable to authenticate form data. Please refresh and try again.');
		$this->token = isset($options['token']) ? $options['token'] : 'missing token';
		$this->logger = isset($options['logger']) ? $options['logger'] : null;
	}



	/**
	 * @param $value
	 * @return bool - true if the value matches the csrf token we have stored
	 */
	public function isValid($value)
	{
		// clear any old errors
		$this->clearErrors();

		// Null == blank, empty string == blank
		if ($value === $this->token)
			return true;

		// The token did not match, which suggests a CSRF attack was attempted
		// Do not validate the form
		if ($this->logger != null)
			$this->logger->warning("Form was submitted with an invalid CSRF token");

		// Add an error message
		$this->addError(self::MsgInvalidToken);
		return false;
	}
}
