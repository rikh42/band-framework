<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

use snb\config\ConfigSettings;
use snb\core\KernelInterface;
use snb\exceptions\CircularReferenceException;
use snb\config\ConfigStoreCompiler;

use Symfony\Component\Yaml\Yaml;

/**
 * manages the config settings of the app
 */
class CachedConfigSettings extends ConfigSettings
{
    protected $configPath;

    public function __construct(KernelInterface $kernel)
    {
        // Perform the default construction
        parent::__construct($kernel);

        // Default the cached path to something sane
        // though we recommend that this be overridden in the service setup
        $this->setCacheFilename($this->getDefaultCacheFilename());
    }


    /**
     * Sets the path that the config file will be read and written to
     * @param $path
     */
    public function setCacheFilename($path)
    {
        $this->configPath = $path;
    }


    /**
     * Loads in a yaml config file, flattens it and stores the results
     * @param  string $resource - the name of the file resource to load
     */
    public function load($resource)
    {
        // Look for the cached config and try and use that
        if (file_exists($this->configPath)) {
            // The file has been generated and exists, so include it
            require $this->configPath;

            // create the store object and copy the data from it
            $configStore = new ConfigStore();
            $this->all = $configStore->getSettings();
        } else {
            // Load the resource and set up the config
            $this->loadResource($resource);

            // write the config settings to the cache class?
            $this->writeCachedClass();
        }
    }

    /**
     * Generates a PHP class that represents the current config and
     * writes it out to the cache path. Future runs should be able to
     * bypass the config file parsing and use the class directly.
     */
    public function writeCachedClass()
    {
        $store = new ConfigStoreCompiler($this->kernel);
        $store->setValues($this->all);
        $store->setCachePath($this->configPath);
        $store->compile();
    }



    /**
     * Gets the full path to the cached config file settings
     * We need to know this path before the config is loaded
     * (so we can load the cached config), so we can't store a location
     * for the cached file inside the config file.
     * @return string|void
     */
    protected function getDefaultCacheFilename()
    {
        // start by putting it in the app folder
        $path = $this->kernel->getPackagePath('app');

        // Find out the environment (strip out all characters that aren't a-z,
        // as we are using it to create a filename)
        $env = preg_replace('/[^a-z]/i', '', $this->kernel->getEnvironment());

        // add in the filename of the cache file...
        $path .= "/ConfigCache.$env.php";

        // That should do...
        return $path;
    }


    /**
     * Clear the cache
     * We attempt to remove the config cache file that we might have created
     */
    public function clearCache()
    {
        // Find the path of the cached file and try and delete it.
        if (file_exists($this->configPath) && is_file($this->configPath)) {
            @unlink($this->configPath);
        }
    }
}
