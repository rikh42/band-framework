<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;

use snb\core\KernelInterface;
use snb\http\Request;
use snb\http\Response;
use snb\core\ServiceContainer;
use snb\core\ServiceDefinition;
use snb\logger\BufferedHandler;
use snb\logger\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use snb\events\RequestEvent;



/**
 * Kernel
 * The core class of the app. Contains info about all the registered
 * bundles and services, and handles routing requests to controllers and
 * dealing with the response
 */
class Kernel implements KernelInterface
{
	protected $booted;
	protected $container;
	protected $settings;
	protected $environment;
	protected $packages;
	protected $logger;

	/**
	 * set up the Kernel
	 * @param string $env - the name of the environment (dev, prod, test)
	 * @param $starttime - start time of the app in microseconds (used in debug builds)
	 */

	/**
	 * @param $env
	 * @param $starttime
	 */
	public function __construct($env, $starttime)
	{
		$this->booted = false;
		$this->logger = new Logger(new BufferedHandler());
		$this->logger->logTime('Creating Kernel');
		$this->container = new ServiceContainer();
		$this->environment = $env;
		$this->packages = array();
	}




	/**
	 * boot
	 * Called during startup to register all the components of the app
	 * and get ready to handle a request
	 */
	public function boot()
	{
		// Don't do this is we have already booted up
		if ($this->booted)
			return;

		// register all the packages being used
		$this->initPackages();

		// Register services etc
		$this->initServices();

		// find other objects that want to boot
		foreach ($this->getBootable() as $bootable)
		{
			// Attach the container if needed
			if ($bootable instanceof ContainerAware)
				$bootable->setContainer($this->container);

			// give the package a change to boot
			if ($bootable instanceof PackageInterface)
				$bootable->boot();
		}

		// we're done
		$this->booted = true;
	}


	/**
	 * Calls the AppKernel to find the list of packages being used
	 */
	protected function initPackages()
	{
		// find all the packages the app is using
		$this->packages = $this->registerPackages();
	}


	/**
	 * Dummy implementation in case AppKernel fails to overide it
	 * @return array
	 */
	protected function registerPackages()
	{
		return array();
	}


	/**
	 * Gets a list of objects that also need to have boot called on them
	 * @return array
	 */
	protected function getBootable()
	{
		return array();
	}


	/**
	 * initServices
	 * Called from boot to set up all the services on the system
	 */
	protected function initServices()
	{
		// load the config and add it as a service
		$config = new ConfigSettings();
		$configPath = $this->findResource($this->getConfigName(), 'config');
		$config->load($configPath);

		// Add some services that are part of the system
		$this->addService('config', $config);

		// Add some services that are part of the system
		$this->addService('kernel', $this);

		// Add the event dispatcher
		$this->addService('event-dispatcher', new EventDispatcher);

		// Create a logger
		$this->addService('logger', $this->logger);

		// Add the template engine service
		$this->addService('template.engine', 'snb\view\TwigView');

		// Add the database engine as a service
		$this->addService('database', 'snb\core\Database')
			->addCall('init', array());

		// Register some app specific services
		$this->registerServices();
	}



	/**
	 * getConfigName
	 * Gets the default name of the config file to load.
	 * Override this to specify a different config file.
	 * @return string
	 */
	protected function getConfigName()
	{
		return '::config.yml';
	}




	/**
	 * registerServices
	 * Empty implementation - should be overridden by in AppKernel
	 * this function returns an array of services to be registered
	 * @return array
	 */
	protected function registerServices()
	{
		return array();
	}




	/**
	 * @param string $name - the name of the service to add
	 * @param $ref - it's classname or an instance of the service
	 * @return ServiceDefinition
	 */
	public function addService($name, $ref)
	{
		$service = new ServiceDefinition($name, $ref);
		$this->container->set($name, $service);
		return $service;
	}



	/**
	 * Adds a model
	 * @param array $models - an array of name to classnames
	 */
	public function addModels(array $models)
	{
		foreach ($models as $name=>$class)
			$this->container->setModel($name, $class);
	}




	/**
	 * Finds the path of the named package, or the app path if no package is given
	 * @param $name
	 * @return string
	 */
	public function getPackagePath($name)
	{
		// if there is no name, switch to using the app
		// domain and hope that the app has been defined.
		if (empty($name))
			$name = 'app';

		// Look for the package by name
		foreach ($this->packages as $package => $path)
		{
			if ($package == $name)
				return $path;
		}

		// failed to find it
		$this->logger->warning('Failed to find package : '.$name);
		return '';
	}




	/**
	 * Turns a system pathname (eg example:/cache) into a full path
	 * eg /app/project/path/example/src/cache
	 * @param string $name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function findPath($name)
	{
		// See if the name looks like an absolute path
		if (strpos($name, '/') === 0)
			return $name;

		// Not an absolute path, so assume it is in the resource file format
		// package:path
		// check that it looks like a proper resource filename
		if (!preg_match('/^(.*):(.+)$/', $name, $regs))
			return $name;

		// Name matched the resource filename pattern
		// we've split it up into the component parts
		$path = $this->getPackagePath($regs[1]);
		if (!empty($regs[2]))
			$path .= $regs[2];

		// Does the path exist?
		if (!is_dir($path))
		{
			// nope, try and make it.
			mkdir($path, 0777, true);
		}

		return $path;
	}


	/**
	 * findResource
	 * Given a resource filename, turn it into a full path name
	 * @param string $name - name of the resource to load in form package:group:file
	 * @param string $type - the type of resource (eg views, translations, config)
	 * @return string - the full path of the file
	 * @throws \InvalidArgumentException
	 */
	public function findResource($name, $type)
	{
		// See if the name looks like an absolute path
		if (strpos($name, '/') === 0)
			return $name;

		// Not an absolute path, so assume it is in the resource file format
		// package:group:file
		// check that it looks like a proper resource filename
		$path = '';
		if (preg_match('/^(.*):(.*):(.+)$/', $name, $regs))
		{
			// Name matched the resource filename pattern
			// we've split it up into the component parts
			$path = $this->getPackagePath($regs[1]);
			if (!empty($path))
			{
				$path .= '/resources/'.$type;
				if (!empty($regs[2]))
					$path .= '/'.$regs[2];
				$path .= '/'.$regs[3];
			}
		}

		// Does the file exist?
		if (!file_exists($path))
		{
			// nope, to log an error
			$this->logger->error('findResource failed to find a file. Generated filename is \''.$path.'\'',
					array('$name'=>$name, '$type'=>$type, '$path'=>$path));

			// throw an exception
			throw new \InvalidArgumentException(sprintf('Unable to find file "%s" => "%s".', $name, $path));
		}

		return $path;
	}




	/**
	 * @return EventDispatcherInterface
	 */
	public function getDispatcher()
	{
		return $this->container->get('event-dispatcher');
	}




	/**
	 * handle
	 * This is really the heart of the application.
	 * This function is called with a request. it routes the request
	 * off to the appropriate controller and collects a response,
	 * which is returns
	 * @param \snb\http\Request $request
	 * @return \snb\http\Response
	 */
	public function handle(Request $request)
	{
		try
		{
			// Boot the kernel if it hasn't already happened
			$this->boot();

			// add the session to the request, if we have one defined
			$request->setSession($this->container->get('session'));

			// place the request into the service container
			$this->container->set('request', $request);
			$config = $this->container->get('config');

			// Get the dispatcher and send an event for the route
			// This is the "is this request OK" event. If you want to
			// implement a firewall, this would be a good event to listen to
			$dispatcher = $this->getDispatcher();
			$requestEvent = new RequestEvent($this, $request);
			$dispatcher->dispatch('kernel.request', $requestEvent);
			if ($requestEvent->hasResponse())
				return $requestEvent->getResponse();

			// load routes and find the route
			$routes = $this->container->get('routes');
			$routesPath = $this->findResource(
				$config->get('snb.routes', '::routes.yml'),
				'config');
			$routes->load($routesPath);

			// Try and find a route that matches the request
			$route = $routes->findMatchingRoute($request);
			if ($route == null)
			{
				// No route found that matches the request,
				// so look for the 404 route instead
				$route = $routes->find('404');
				if ($route == null)
				{
					// No 404 route defined, so we'll have to throw an exception
					throw new \InvalidArgumentException('Could not find a matching route, or a 404 route');
				}
			}


			// Find info on the controller we'll need to call
			$controllerName = $route->getController();
			$actionName = $route->getAction();
			$args = $route->getArguments();

			// try and create the controller
			$controller = new $controllerName();
			if ($controller instanceof ContainerAware)
				$controller->setContainer($this->container);

			$response = null;
			if ($controller->init())
			{
				// call the action on the controller - if it exists
				if (method_exists($controller, $actionName));
				{
					$response = call_user_func_array(array($controller, $actionName), $args);
				}
			}

			// inject any logging data here...
			//$this->logger->dump();

			return $response;
		}
		catch (\Exception $e)
		{
			$msg= 'Exception Caught: <br>';
			$msg.= $e->getMessage() . '<br>';
			$msg.= 'File: '.$e->getFile() . '<br>';
			$msg.= 'Line: '.$e->getLine() . '<br>';
			return new Response($msg, 500);
		}
	}
}

