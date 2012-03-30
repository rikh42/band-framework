<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;
use snb\http\RequestParams;



//==============================
// RequestFiles
// Wrapper for the PHP $_FILES super global
//==============================
class RequestFiles extends RequestParams
{
	//==============================
	// __construct
	// This expects to be passed the $_FILE array, which it will clean
	// up, validate and set up in the RequestParams object, ready for use
	//==============================
	public function __construct($files)
	{
		$this->validate($files);
	}


	//==============================
	// validate
	// Validates the contents of the file array and
	// arranges it into a known good format.
	//==============================
	protected function validate($files)
	{
		// Should be an array.
		if (!is_array($files))
		{
			return;
		}

		// check each entry
		$fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
		foreach($files as $key => $value)
		{
			// Each entry should be an array
			if (!is_array($value))
				continue;

			// with a specific set of keys in it
			$keys = array_keys($value);
			sort($keys);
			if ($keys != $fileKeys)
				continue;

			// See if this is an entry with sub entries in it
			if (is_array($value['name']))
			{
				foreach($value['name'] as $subKey=>$val)
				{
					// create a new uploaded file
					$this->addFile($subKey, array(
						'tmp_name' => $value['tmp_name'][$subKey],
						'name' => $value['name'][$subKey],
						'size' => $value['size'][$subKey],
						'type' => $value['type'][$subKey],
						'error' => $value['error'][$subKey]
					));
				}
			}
			else
			{
				// just a regular entry, so copy it over
				$this->addFile($key, $value);
			}
		}
	}



	//==============================
	// addFile
	// Adds a file to the parameter list, so it can be accessed with has, get etc
	//==============================
	protected function addFile($name, $value)
	{
		// create a new uploaded file
		$file = new UploadedFile($value['tmp_name'], $value['name'], $value['size'],
			$value['type'], $value['error'] );

		// add it to the parameter set
		parent::addItem($name, $file);
	}
}
