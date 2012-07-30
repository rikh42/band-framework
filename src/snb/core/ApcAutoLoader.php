<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

/**
 * ApcAutoLoader
 * Loads in classes based on their namespace and name
 */
class ApcAutoLoader extends AutoLoader
{
    protected $cacheKey = 'ApcAutoLoader';


    /**
     * Sets a string that will be appended to all the APC keys, allowing multiple apps
     * to be running on the same APC setup.
     * Recommend setting this to the app name...
     * @param $key
     */
    public function setCacheKey($key)
    {
        $this->cacheKey = $key;
    }


    /**
     * Called when a class needs to be loaded
     * @param $class - the name of the class
     */
    public function loadClass($class)
    {
        // Find out where the class is stored, from the cache
        $filePath = apc_fetch($this->cacheKey.$class);

        // Did we find it?
        if ($filePath === false) {
            // Not in the cache, so find out where the class is kept
            $filePath = parent::getFilePath($class);

            // store it in the cache
            if ($filePath !== false) {
                apc_store($this->cacheKey.$class, $filePath);
            }
        }

        // include it if we got a hit
        if ($filePath !== false) {
            require $filePath;
        }
    }

}
