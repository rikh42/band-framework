<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use snb\core\ContainerAware;



/**
 * ServiceDefinition
 * Holds a set of information about a service. It collects together things like
 * the classname of the service, and a list of arguments that need to be injected
 * into the class on construction, or post construction, to set it up.
 * It also takes care of the actual creation of objects using this information.
 * The arguments that are passed into objects can either be regular values,
 * settings from the config file, or other service objects.
 */
class ServiceDefinition extends ContainerAware
{
	protected $name;
	protected $reference;		// the class name or an instance of the object
	protected $arguments;
	protected $calls;
	protected $resolved;
	public $isCreating;


	/**
	 * @param string $name - the name of the service (human friendly name)
	 * @param $ref - Either the classname of the service, or an instance of
	 * 					service object.
	 */
	public function __construct($name, $ref)
	{
		$this->name = $name;
		$this->reference = $ref;
		$this->arguments = array();
		$this->calls = array();
		$this->resolved = false;
		$this->isCreating = false;
	}


	/**
	 * setArguments
	 * Allows you to pass in an array of arguments that will be used
	 * during service creation. There are 2 special types of values
	 * that allow you specify using settings from the config file
	 * or another service object.
	 * For a config, pass a string in the current form:-
	 * 		'::config::name.of.config'
	 * For a service, pass a string like this...
	 * 		'::service::name-of-service'
	 * @param array $args - the constructor arguments for the service
	 * @return ServiceDefinition
	 */
	public function setArguments(array $args)
	{
		$this->arguments = $args;
		return $this;
	}


	/**
	 * addCall
	 * Not all objects can be set up via the constructor. Sometimes you need
	 * to call a function on the class just after it has been created.
	 * For example, to call setInfo(42, 'rik') on a service just after it has
	 * been created, do the following:-
	 * 		addCall('setInfo', array(42, 'rik));
	 * The argument list has the same options as setArguments
	 * @param string $func - the name of the function to call
	 * @param array $args - the list of arguments passed to the function
	 */
	public function addCall($func, array $args=array())
	{
		$this->calls[$func] = $args;
	}


	/**
	 * create
	 * Create the object for this service if needed.
	 * This will pass the appropriate data to the constructor and
	 * call any additional functions needed to set it up.
	 * @return mixed
	 */
	public function create(ContainerInterface $container)
	{
		// if we already have an object, use it
		if (is_object($this->reference))
			return $this->reference;

		// nothing, so we'll need to create something
		// first, get all our variables in a form we can pass to the object
		$this->resolveArguments();

		// create the service
		$r = new \ReflectionClass($this->reference);
		$service = $r->newInstanceArgs($this->arguments);

		// If the object is container aware, inject the container
		if ($service instanceof ContainerAware)
		{
			$service->setContainer($container);
		}

		// call any functions needed to set it up
		foreach($this->calls as $func=>$args)
		{
			// call the function
			if ($r->hasMethod($func))
				call_user_func_array(array($service, $func), $args);
		}

		// remember this
		$this->reference = $service;
		return $this->reference;
	}


	/**
	 * resolveArguments
	 * Remaps any config and service values into actual values
	 * @return mixed
	 */
	protected function resolveArguments()
	{
		if ($this->resolved)
			return;

		// Start with the constructor arguments
		foreach ($this->arguments as &$arg)
			$arg = $this->resolveValue($arg);

		// and do the same to all the function call arguments
		foreach ($this->calls as $func => &$args)
		{
			foreach ($args as &$arg)
			{
				$arg = $this->resolveValue($arg);
			}
		}

		// we're done, so don't do it again.
		$this->resolved = true;
	}



	/**
	 * resolveValue
	 * Remaps a single argument into an actual value if
	 * it was a config reference, or a service reference.
	 * @param $arg
	 * @return mixed
	 */
	protected function resolveValue($arg)
	{
		// If it isn't a string, there is nothing to resolve.
		if (!is_string($arg))
			return $arg;

		// See if it matches our argument format. eg
		// ::type::value
		// if it does not match, then pass it on as a plain string
		if (!preg_match('/^::([a-z]*)::(.*)/iu', $arg, $regs))
			return $arg;

		// deal with the value
		if ($regs[1]=='config')
		{
			// return the setting from the config file (regs[2])
			$config = $this->container->get('config');
			return $config->get($regs[2], null);
		}
		else if ($regs[1] == 'service')
		{
			// create an instance of the named service
			return $this->container->get($regs[2]);
		}

		// don't know what this is then, just pass it along unchanged
		return $arg;
	}
}


