<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

/**
 * AutoLoader
 * Loads in classes based on their namespace and name
 */
class AutoLoader
{
    protected $namespaces = array();
    protected $prefix = array();

    /**
     * @param array $namespaces - an array of namespaces
     */
    public function registerNamespaces($namespaces)
    {
        $this->namespaces = array_merge($this->namespaces, $namespaces);
    }

    /**
     * registerPrefixes
     * PEAR Like prefix patterns
     * @param $prefixes
     */
    public function registerPrefixes($prefixes)
    {
        $this->prefix = array_merge($this->prefix, $prefixes);
    }

    /**
     * Gets the list of namespaces
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Register the autoloader
     */
    public function register()
    {
        // register the auto load function
        spl_autoload_register(array($this, 'loadClass'));
    }



    /**
     * Called when a class needs to be loaded
     * @param $class - the name of the class
     */
    public function loadClass($class)
    {
        // Go find the path for the class
        $filePath = $this->getFilePath($class);

        // store it in the cache
        if ($filePath !== false) {
            // include it
            require $filePath;
        }
    }



    /**
     * @param $class
     * @return bool|string
     */
    protected function getFilePath($class)
    {
        // remove leading \
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        // Find the last slash
        $pos = strrpos($class, '\\');
        if ($pos !== false) {
            // Get the namespace
            $namespace = substr($class, 0, $pos);
            foreach ($this->namespaces as $name => $path) {
                // Skip entries in the namespace list that don't start with
                // this items namespace
                if (strpos($namespace, $name) !== 0) {
                    continue;
				}

                // Build the file name that the class should be in
                $className = substr($class, $pos + 1);
                $file = $path."/".str_replace('\\', "/", $namespace)."/".$className.'.php';

                // Hopefully, found it
                if (file_exists($file)) {
                    return $file;
                }
            }
        } else {
            // PEAR-like Class_Names_With_Underscore
            foreach ($this->prefix as $name => $path) {
                // If this prefix is not a match to the start of the class name, move on
                if (strpos($class, $name) !== 0) {
                    continue;
				}

                // Build the path where we can find the class
                $file = $path."/".str_replace('_', "/", $class).'.php';

                // Hopefully, found it
                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        return false;
    }
}
