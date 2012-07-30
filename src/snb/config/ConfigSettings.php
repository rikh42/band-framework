<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

use snb\config\ConfigInterface;
use snb\core\KernelInterface;
use snb\exceptions\CircularReferenceException;
use snb\config\ConfigStoreCompiler;

use Symfony\Component\Yaml\Yaml;

/**
 * manages the config settings of the app
 */
class ConfigSettings implements ConfigInterface
{
    protected $all;
    protected $loading;
    protected $kernel;

    /**
     * sets up the config store.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->all = array();
        $this->loading = array();
        $this->kernel = $kernel;
    }

    /**
     * Returns the value of the named setting
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!array_key_exists($name, $this->all)) {
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
     * @param  string $name
     * @return bool   - true if the named setting exists, false if not
     */
    public function has($name)
    {
        return array_key_exists($name, $this->all);
    }

    /**
     * removes an item from the settings
     * @param  string $name
     * @return mixed
     */
    public function remove($name)
    {
        // is it in there?
        if (!$this->has($name)) {
            return;
		}

        // yep, so remove it.
        unset($this->all[$name]);
    }

    /**
     * Loads in a yaml config file, flattens it and stores the results
     * @param  string $resource - the name of the file resource to load
     */
    public function load($resource)
    {
        // Load the resource and set up the config
        $this->loadResource($resource);
    }




    /**
     * @param $resource
     * @throws \snb\exceptions\CircularReferenceException
     */
    protected function loadResource($resource)
    {
        // Check that we aren't in a circular loading loop
        // (ie, we load config.yml and it wants to import config.yml)
        if (isset($this->loading[$resource])) {
            throw new CircularReferenceException();
        }

        // we are now loading this file, so protect against loading it twice
        $this->loading[$resource] = true;

        // try and find the file
        $configPath = $this->kernel->findResource($resource, 'config');

        // Read in the content (file or string)
        $content = Yaml::parse($configPath);

        // bad data turns into an empty result
        if ($content == null) {
            $content = array();
        }

        // must be an array, so trash anything else
        if (!is_array($content)) {
            $content = array();
        }

        // Flatten the content down
        $flat = array();
        $this->flatten($content, $flat);

        // See if the file includes an import command
        if (array_key_exists('import', $flat)) {
            // Yes, so load that first
            $this->loadResource($flat['import']);
        }

        // replace any items already set in the config, with the items from this file
        $this->all = array_replace($this->all, $flat);

        // no longer loading this file
        unset($this->loading[$resource]);
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
     * @param null  $path - the current key path
     */
    protected function flatten(array &$from, array &$flat, $path = null)
    {
        foreach ($from as $key => $value) {
            $key = mb_strtolower($key);
            $newpath = $path ? $path.'.'.$key : $key;
            if (is_array($value)) {
                $this->flatten($value, $flat, $newpath);
			} else {
                $flat[$newpath] = $value;
			}
        }
    }


    /**
     * Clear the cache - we don't store anything in a cache, so this does nothing
     */
    public function clearCache()
    {

    }

}
