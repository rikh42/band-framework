<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

use snb\http\Request;
use snb\core\Kernel;
use snb\http\Response;



class AppKernel extends Kernel
{
	/**
	 * Defines the location of the packages in the app
	 * @return array
	 */
	protected function registerPackages()
	{
		return array(
			'app' 		=> __DIR__,
			'snb' 		=> __DIR__.'/../src/snb',
			'example' 	=> __DIR__.'/../src/example'
		);
	}


	/**
	 * Returns any packages that want to be booted
	 * @return array
	 */
	protected function getBootable()
	{
		return array(new \example\ExamplePackage());
	}




	/**
	 * Called to add any services the app wants to register
	 */
	protected function registerServices()
	{
        /*
        $this->addService('cache', 'snb\cache\MemcachedCache')->setArguments(array(
            '::config::snb.cache.host',
            '::config::snb.cache.port',
            '::config::snb.cache.prefix'
        ));

        $this->addService('config', 'snb\config\CachedConfigSettings')
            ->setArguments(array('::service::kernel'))
            ->addCall('load', array($this->getConfigName()));
        */
	}
}
