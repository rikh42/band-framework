<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;

use snb\core\ConfigInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * manages the config settings of the app
 */
class ConfigSettings implements ConfigInterface
{
	protected $all;


	/**
	 * sets up the config store.
	 */
	public function __construct()
	{
		$this->all = array();
	}


	/**
	 * Returns the value of the named setting
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		if (!array_key_exists($name, $this->all))
		{
			return $default;
		}

		return $this->all[$name];
	}




	/**
	 * Sets a named value
	 * @param string $name
	 * @param $value
	 */
	public function set($name, $value)
	{
		$this->all[$name] = $value;
	}



	/**
	 * @param string $name
	 * @return bool - true if the named setting exists, false if not
	 */
	public function has($name)
	{
		return array_key_exists($name, $this->all);
	}




	/**
	 * removes an item from the settings
	 * @param string $name
	 * @return mixed
	 */
	public function remove($name)
	{
		// is it in there?
		if (!$this->has($name))
			return;

		// yep, so remove it.
		unset($this->all[$name]);
	}




	/**
	 * Loads in a yaml config file, flattens it and stores the results
	 * in the config
	 * @param $file
	 */
	public function load($file)
	{
		// Read in the content (file or string)
		$content = Yaml::parse($file);

		// bad data turns into an empty result
		if ($content == null)
			$content = array();

		// must be an array, so trash anything else
		if (!is_array($content))
			$content = array();

		// Flatten the content down
		$flat = array();
		$this->flatten($content, $flat);
		$this->all = array_replace($this->all, $flat);
	}


	/**
	 * flatten
	 * Given a nested array, convert it to a flat array with
	 * names that use the . convention
	 * eg array('name' => array('first'=>'bob', 'surname'=>'smith'))
	 * becomes name.first => bob, name.surname => smith
	 * Also converts all keys to lower case
	 * @param array $from - the nested array
	 * @param array $flat - the array to store the flattened array in
	 * @param null $path - the current key path
	 */
	protected function flatten(array &$from, array &$flat, $path = null)
	{
		foreach ($from as $key => $value)
		{
			$key = mb_strtolower($key);
			$newpath = $path ? $path.'.'.$key : $key;
			if (is_array($value))
				$this->flatten($value, $flat, $newpath);
			else
				$flat[$newpath] = $value;
		}
	}


}