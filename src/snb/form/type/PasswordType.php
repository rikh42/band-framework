<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
/* This file based on part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 */



namespace snb\form\type;
use snb\form\type\FieldType;


/**
 * Represents a password field in a form
 */
class PasswordType extends FieldType
{


	/**
	 * @return string
	 */
	public function getType()
	{
		return 'password';
	}


	/**
	 * Builds the FormView for the control. Passwords are special
	 * and the value field is never sent to the view. ie, password
	 * input elements always set value="" in a form
	 * @return \snb\form\FormView
	 */
	public function getView()
	{
		// Do the normal thing
		$view = parent::getView();

		// then trash the value in the view, as passwords
		// should not appear in the html
		$view->set('value', '');
		return $view;
	}

}
