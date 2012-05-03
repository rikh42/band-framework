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
 * Validate that the field is an Email Address
 */
class Email extends AbstractValidator
{
	const MsgNotEmail = 'notemail';

	public function __construct($options = null)
	{
		parent::__construct($options);
		$this->setMessage(self::MsgNotEmail, 'This is not a valid email address.');
	}



	/**
	 * Check that it is a valid email address
	 * @param $value
	 * @return bool
	 */
	public function isValid($value)
	{
		// clear any old errors
		$this->clearErrors();

		// validate that it is an email address
		if (filter_var($value, FILTER_VALIDATE_EMAIL) === false)
		{
			$this->addError(self::MsgNotEmail);
			return false;
		}

		// Looks good to me...
		return true;
	}
}
