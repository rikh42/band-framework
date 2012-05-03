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
use snb\form\type\TextType;


/**
 * Date field. No support for timezones and custom formatting yet
 */
class DateType extends FieldType
{
	/**
	 * Gets the html type of the field
	 * @return string
	 */
	public function getType()
	{
		return 'date';
	}


	/**
	 * Converts the value into text, ready for the control to display
	 * @return \snb\form\FormView
	 */
	public function getView()
	{
		$view = parent::getView();

		// Need to convert the date time object to text
		$date = $view->get('value');
		if ($date instanceof \DateTime)
		{
			$view->set('value', $date->format('j M Y'));
		}

		return $view;
	}



	/**
	 * Attempts to parse the data being bound to the control into a DateTime object
	 * if it fails, then the value is set to null.
	 * @param $data
	 */
	public function bind($data)
	{
		try
		{
			$this->set('value', new \DateTime($data));
		}
		catch(\Exception $e)
		{
			$this->set('value', null);
		}
	}

}