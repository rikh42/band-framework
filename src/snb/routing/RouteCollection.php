<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\routing;
use snb\routing\UrlGeneratorInterface;
use snb\core\ContainerAware;
use snb\routing\Route;
use snb\http\Request;
use Symfony\Component\Yaml\Yaml;

//==============================
// RouteCollection
// A collection of all the routes we know about
//==============================
class RouteCollection extends ContainerAware implements UrlGeneratorInterface
{
    protected $routes;

    public function __construct()
    {
        $this->routes = array();
    }

    //==============================
    // getAllRoutes
    // Gets the array of routes. Careful now...
    //==============================
    public function getAllRoutes()
    {
        return $this->routes;
    }

    //==============================
    // find
    // Finds a named route, if it exists
    //==============================
    public function find($name)
    {
        // If the route exists, try and generate a url with it
        if (array_key_exists($name, $this->routes)) {
            return $this->routes[$name];
        }

        // what should we return if the route does not exist?
        return null;
    }

    //==============================
    // generate
    // builds a url using a named route and argument array
    //==============================
    public function generate($name, $arguments=array(), $fullyQualified=false)
    {
        // If the route exists, try and generate a url with it
        $url = '';
        $route = $this->find($name);
        if ($route) {
            $url = $route->generate($arguments);
        }

        // if they want a fully qualified path, then we may need to
        // find out the domain
        if ($fullyQualified) {
            $request = $this->container->get('request');
            if ($request) {
                $url = $request->getHttpHost() . $url;
            }
        }

        return $url;
    }

    /**
     * Finds a route that matches the request
     * @param  \snb\http\Request $request
     * @return Route
     */
    public function findMatchingRoute(Request $request)
    {
        // go though all the routes in order and return the first matching one
        $path = urldecode($request->getPath());
        foreach ($this->routes as $route) {
            if ($route->isMatch($path, $request)) {
                return $route;
            }
        }

        return null;
    }

    //==============================
    // load
    // Loads all the routes in the named file and replaces any existing routes with them.
    // The file is expected to be in simple json format
    //==============================
    public function load()
    {
        // reset the current route list
        $this->routes = array();

        // find the routes resource
        $config = $this->container->get('config');
        $routesResource = $config->get('snb.routes', '::routes.yml');
        $cacheKey = 'RouteCollection::'.$routesResource;

        // Try and load the routes from the cache
        $cache =  $this->container->get('cache');
        $routes = $cache->get($cacheKey);
        if ($routes == null) {
            // Load the routes from the resource and cache it
            $routes = $this->loadFromResource($routesResource);
            $cache->set($cacheKey, $routes, 3600);
        }

        // Update the route table
        $this->routes = $routes;
    }


    /**
     * @param $resourceName
     * @return array
     */
    public function loadFromResource($resourceName)
    {
        // Was not in the cache, so load it from disk and write it to the cache
        $routes = array();

        // Find the name of the routes file
        $kernel = $this->container->get('kernel');
        $filename = $kernel->findResource($resourceName, 'config');

        // Load in the routes
        $content = Yaml::parse($filename);
        if (($content == null) || (!is_array($content))) {
            return $routes;
        }

        // try and set up the routes from this content
        foreach ($content as $name => $item) {
            // Route without a url
            if (!isset($item['url'])) {
                continue;
            }

            // get the values from the array and set up defaults
            $options = isset($item['options']) ? $item['options'] : array();
            $defaults = isset($item['defaults']) ? $item['defaults'] : array();
            $placeholders = isset($item['placeholders']) ? $item['placeholders'] : array();

            // Route without a controller can also be ignored
            if (!isset($options['controller'])) {
                continue;
            }

            // create the route
            $route = new Route($name, $item['url'], $options, $placeholders, $defaults);
            $routes[$name] = $route;
        }

        return $routes;
    }
}
