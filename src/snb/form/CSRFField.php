<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\form;
use snb\form\HiddenField;




//==============================
// CSRFField
// A special type of hidden field that helps implement
// a CSRF protection system
// https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29_Prevention_Cheat_Sheet
// Generates a token, based on the sessionid and a form specific secret.
// Requires that the token comes back when the form is submitted. Ensures that the form has been
// posted back by the person we issued it to in the first place.
//==============================
class CSRFField extends HiddenField
{
	protected $sessionID;
	protected $secret;
	protected $hash;



	//==============================
	// __construct
	// Sets up the field and generates the secret token
	//==============================
	function __construct($name, $sessionID, $secret)
	{
		parent::__construct($name);
		$this->secret = $secret;
		$this->sessionID = $sessionID;
		$this->hash = $this->generateHash();
		$this->value = $this->hash;
	}


	//==============================
	// generateHash
	// Actually performs the calculation to convert our secret string and session id
	// into a secure random looking token / hash
	//==============================
	protected function generateHash()
	{
		return sha1($this->secret.'We do our best to keep the baddies away'.$this->sessionID);
	}



	//==============================
	// isValid
	// verifies that the calculated hash token matches the one in the form
	//==============================
	function isValid()
	{
		if ($this->value != $this->hash)
		{
			// As this field is hidden, we really want to pass the error up to the form
			if ($this->form)
			{
				$this->form->setError('form/errors/csrf');
			}

			// Set the error on the field as well, as we may as well
			$this->setError('form/errors/csrf');
			return false;
		}

		return parent::isValid();
	}
}

