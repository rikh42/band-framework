<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\form\type;
use snb\form\type\HiddenType;
use snb\form\validators\CsrfValidator;


class CsrfType extends HiddenType
{
	protected $token;


	/**
	 * @param string $secret
	 * @param string $formName
	 */
	public function __construct($secret, $formName='')
	{
		// Do the normal thing
		parent::__construct();

		// Generate a token that is unique to this session
		$sessionId = session_id();
		$this->token = sha1($secret.'Generates a token for the form'.$sessionId.$formName);

		// Set up the field
		$this->set('name', 'csrf_token');
		$this->set('value', $this->token);

		// Finally, we need to add a validator to the field
		// to ensure that the token is validated when the form is submitted
		$v= new CsrfValidator(array('token' => $this->token));
		$this->addValidator($v);
	}


	protected function readFromObject($object)
	{
		// Does nothing, as you can't override the default values
	}

	protected function writeToObject($object)
	{
		// Does nothing, as we don't want the csrf token showing up in the form results
	}
}