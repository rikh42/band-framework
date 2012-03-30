<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;

use snb\core\ContainerInterface;
use snb\core\ConfigSettings;
use snb\exceptions\CircularReferenceException;


/**
 * Service Container
 * This class holds all the services that might be available to an app
 * A service is just a class that acts like a singleton
 * eg. $container->get('database'); // return the one and only database engine
 */
class ServiceContainer implements ContainerInterface
{
	protected $services;
	protected $models;


	public function __construct()
	{
		$this->services = array();
		$this->models = array();
	}



	/**
	 * set
	 * Sets the named service
	 * @param string $name - the name of the service
	 * @param mixed $object - either the classname of the object, or the instance of the object
	 */
	public function set($name, $object)
	{
		$this->services[$name] = $object;
	}


	/**
	 * setMany
	 * Adds an array of services to the container, in one go
	 * @param array $list - a list of name/value pairs of services to add
	 */
	public function setMany($list)
	{
		if (is_array($list))
		{
			foreach($list as $name=>$object)
			{
				$this->set($name, $object);
			}
		}
	}



	/**
	 * get
	 * Get the named service object. If it hasn't been created yet, it will be created
	 * @param string $name - the name of the service you want
	 * @return object|null
	 */
	public function get($name)
	{
		// if the feature exists, return it
		if (array_key_exists($name, $this->services))
		{
			// get the named object
			$object = $this->services[$name];

			// If the object has already been created and set up, return it.
			if (!($object instanceof ServiceDefinition))
				return $object;

			// prevent circular references
			if ($object->isCreating)
			{
				throw \CircularReferenceException();
			}

			// create the object
			$object->setContainer($this);
			$object->isCreating = true;
			$instance = $object->create($this);

			// finally return it
			$this->services[$name] = $instance;
			$object->isCreating = false;
			return $instance;
		}

		// else nothing
		return null;
	}


	/**
	 * Magic get function, so you can also go $container->database
	 * @param $name
	 * @return object|null
	 */
	public function __get($name)
	{
		return $this->get($name);
	}



	/**
	 * Sets a model name into the container
	 * @param $name
	 * @param $ref
	 */
	public function setModel($name, $ref)
	{
		$this->models[$name] = $ref;
	}


	/**
	 * Creates an instance of a model
	 * @param string $name - the name of the model to create
	 * @return null
	 */
	public function createModel($name)
	{
		// Check the model exists, and if not, fail
		if (array_key_exists($name, $this->models))
		{
			// Use the class name in the container
			$modelClass = $this->models[$name];
		}
		else
		{
			// if the name looks like a class name, assume it is
			if (mb_strpos($name, '\\') === false)
				return null;

			// treat this as the class name
			$modelClass = $name;
		}

		// create the model
		$model = new $modelClass;
		if ($model instanceof ContainerAware)
			$model->setContainer($this);

		// Call init, if it exists.
		$r = new \ReflectionClass($model);
		if ($r->hasMethod('init'))
			$model->init();

		return $model;
	}
}
