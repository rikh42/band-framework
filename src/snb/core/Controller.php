<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;

use snb\core\ContainerAware;
use snb\http\Response;

use snb\core\DatabaseInterface;
use snb\http\Request;
use snb\logger\LoggerInterface;
use snb\form\FormBuilder;
use snb\security\Auth;


//==============================
// Controller
// Base class for controllers.
//==============================
class Controller extends ContainerAware
{
	//==============================
	// init
	// Called once the object has been created and features added.
	// Allows sub-classes to hook into this process and add in any features they need access to
	//==============================
	public function init()
	{
		return true;
	}


	/**
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function render($name, array $data = array())
	{
		return $this->container->get('template.engine')->render($name, $data);
	}


	/**
	 * Render a view and store the result in a response object (created if needed)
	 *
	 * @param string $name
	 * @param array $data
	 * @param null|\snb\http\Response $response
	 *
	 * @return \snb\http\Response
	 */
	public function renderResponse($name, array $data=array(), Response $response=null)
	{
		// create a response, if one wasn't provided
		if ($response==null)
		{
			$response = new Response();
		}

		// render the view into the response and return it
		$response->setContent($this->render($name, $data));
		return $response;
	}



	/**
	 * Creates or updates a response to be a redirect to the named route
	 * @param string $routeName
	 * @param array $args
	 * @param null|\snb\http\Response $response
	 * @return null|\snb\http\Response
	 */
	public function redirectResponse($routeName, array $args=array(), Response $response = null)
	{
		// create a response, if one wasn't provided
		if ($response == null)
		{
			$response = new Response();
		}

		// Find the route mentioned
		$routeCollection = $this->getRoutes();
		if ($routeCollection)
		{
			$route = $routeCollection->find($routeName);
			if ($route)
			{
				$response->setRedirectToRoute($route, $args, $this->getRequest());
			}
		}

		// return it.
		return $response;
	}





	/**
	 * @return \snb\view\ViewInterface
	 */
	public function getView()
	{
		return $this->container->get('template.engine');
	}

	/**
	 * @return \snb\core\DatabaseInterface
	 */
	public function getDatabase()
	{
		return $this->container->get('database');
	}

	/**
	 * @return \snb\http\Request
	 */
	public function getRequest()
	{
		return $this->container->get('request');
	}


	/**
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->container->get('logger');
	}


	/**
	 * @return \snb\routing\RouteCollection
	 */
	public function getRoutes()
	{
		return $this->container->get('routes');
	}


	/**
	 * @return snb\form\FormBuilder
	 */
	public function getFormBuilder()
	{
		return $this->container->get('form.builder');
	}



	/**
	 * @return snb\security\Auth
	 */
	public function getAuth()
	{
		return $this->container->get('auth');
	}

	/**
	 * @return \snb\cache\CacheInterface
	 */
	public function getCache()
	{
		return $this->container->get('cache');
	}


	/**
	 * get
	 * Allows services to be accessed without having to use the container
	 * eg, use $this->get('database'), instead of $this->container->get('database');
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		return $this->container->get($name);
	}
}


