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
use snb\events\RouteEvent;
use snb\events\ResponseEvent;
use snb\events\NoResponseFromControllerEvent;
use snb\events\ExceptionOnRequestEvent;
use snb\exceptions\PageNotFoundException;
use snb\errors\ErrorHandler;
use snb\errors\ExceptionHandler;

/**
 * Kernel
 * The core class of the app. Contains info about all the registered
 * bundles and services, and handles routing requests to controllers and
 * dealing with the response
 */
class Kernel extends ContainerAware implements KernelInterface
{
    protected $booted;
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
     */
    public function __construct($env)
    {
        // set some sensible defaults.
        $this->booted = false;
        $this->packages = array();
        $this->environment = preg_replace('/[^a-z0-9]/', '', mb_strtolower($env));

        // register the error handlers
        $this->registerErrorHandlers();

        // Create the service container and logger
        $this->setContainer(new ServiceContainer());
        $this->logger = new Logger(new BufferedHandler());
        $this->logger->logTime('Creating Kernel');
    }

    /**
     * Registers the system error handlers.
     */
    public function registerErrorHandlers()
    {
        // use the standard error handlers
        // If you want to use different ones, override this function and do as you please.
        $e = ExceptionHandler::register();
        ErrorHandler::register($e);
    }

    /**
     * boot
     * Called during startup to register all the components of the app
     * and get ready to handle a request
     */
    public function boot()
    {
        // Don't do this is we have already booted up
        if ($this->booted) {
            return;
        }

        // register all the packages being used
        $this->initPackages();

        // Register services etc
        $this->initServices();

        // find other objects that want to boot
        foreach ($this->getBootable() as $bootable) {
            // Attach the container if needed
            if ($bootable instanceof ContainerAware) {
                $bootable->setContainer($this->container);
            }

            // give the package a change to boot
            if ($bootable instanceof PackageInterface) {
                $bootable->boot($this);
            }
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
        $config->load($this->getConfigName(), $this);

        // Add some services that are part of the system
        $this->addService('config', $config);
        $this->addService('kernel', $this);
        $this->addService('routes', 'snb\routing\RouteCollection');
        $this->addService('event-dispatcher', new EventDispatcher);
        $this->addService('logger', $this->logger);
        $this->addService('template.engine', 'snb\view\TwigView');
        $this->addService('database', 'snb\core\Database')->addCall('init', array());
        $this->addService('session', 'snb\http\SessionStorage')->addCall('start');
        $this->addService('auth', 'snb\security\Auth')->setArguments(array('::service::auth.token', '::service::auth.context', '::service::event-dispatcher'));
        $this->addService('auth.token', 'snb\security\SecurityToken');
        $this->addService('auth.context', 'snb\security\SecurityContext');
        $this->addService('auth.userprovider', 'snb\security\DatabaseUserProvider')->setArguments(array('::service::database'));
        $this->addService('form.builder', 'snb\form\FormBuilder');
        $this->addService('twig.extension.routing', 'snb\view\RouteExtension')->setArguments(array('::service::routes'));
        $this->addService('twig.extension.forms', 'snb\view\FormExtension')->setArguments(array('::service::config'));
        $this->addService('db.migrate', 'snb\core\Migrate')->setArguments(array('::service::database', '::service::logger'))->addCall('ensureMigrationTable');
        $this->addService('cache', 'snb\cache\NullCache');
        $this->addService('email', 'snb\email\NullEmail')->setMultiInstance();

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
        return '::config-'.$this->environment.'.yml';
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
     * Adds a model - basically the same as addService, only with a call to setMultiInstance()
     * built in for good measure.
     * @param $name - name of the model (lower case and dots)
     * @param $ref - the name of the class to use for this model
     * @return ServiceDefinition
     */
    public function addModel($name, $ref)
    {
        // same as addService
        $modelDef = $this->addService($name, $ref);

        // Only we say we are not a singleton
        $modelDef->setMultiInstance();

        // return the model definition for chaining
        return $modelDef;
    }



    /**
     * Gain access to the container
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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
        if (empty($name)) {
            $name = 'app';
        }

        // Look for the package by name
        foreach ($this->packages as $package => $path) {
            if ($package == $name) {
                return $path;
            }
        }

        // failed to find it
        $this->logger->warning('Failed to find package : '.$name);

        return '';
    }




    /**
     * Turns a system pathname (eg example:/cache) into a full path
     * eg /app/project/path/example/src/cache
     * @param  string                    $name
     * @return string
     * @throws \InvalidArgumentException
     */
    public function findPath($name)
    {
        // See if the name looks like an absolute path
        if (strpos($name, '/') === 0) {
            return $name;
        }

        // Not an absolute path, so assume it is in the resource file format
        // package:path
        // check that it looks like a proper resource filename
        if (!preg_match('/^(.*):(.+)$/', $name, $regs)) {
            return $name;
        }

        // Name matched the resource filename pattern
        // we've split it up into the component parts
        $path = $this->getPackagePath($regs[1]);
        if (!empty($regs[2])) {
            $path .= $regs[2];
        }

        // Does the path exist?
        if (!is_dir($path)) {
            // nope, try and make it.
            if (!@mkdir($path, 0777, true)) {
                throw new \ErrorException("Kernel::findPath() Failed to create directory $path from $name");
            }
        }

        return $path;
    }

    /**
     * findResource
     * Given a resource filename, turn it into a full path name
     * @param  string                    $name - name of the resource to load in form package:group:file
     * @param  string                    $type - the type of resource (eg views, translations, config)
     * @return string                    - the full path of the file
     * @throws \InvalidArgumentException
     */
    public function findResource($name, $type)
    {
        // See if the name looks like an absolute path
        if (strpos($name, '/') === 0) {
            return $name;
        }

        // Not an absolute path, so assume it is in the resource file format
        // package:group:file
        // check that it looks like a proper resource filename
        $path = '';
        if (preg_match('/^(.*):(.*):(.+)$/', $name, $regs)) {
            // Name matched the resource filename pattern
            // we've split it up into the component parts
            $path = $this->getPackagePath($regs[1]);
            if (!empty($path)) {
                $path .= '/resources/'.$type;
                if (!empty($regs[2])) {
                    $path .= '/'.$regs[2];
                }

                $path .= '/'.$regs[3];
            }
        }

        // Does the file exist?
        if (!file_exists($path)) {
            // nope, to log an error
            $this->logger->error('findResource failed to find a file. Generated filename is \''.$path.'\'',
                    array('$name'=>$name, '$type'=>$type, '$path'=>$path));

            // throw an exception
            throw new \InvalidArgumentException(sprintf('Unable to find file "%s - %s".', $name, $path));
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
     * @param  \snb\http\Request  $request
     * @return \snb\http\Response
     */
    public function handle(Request $request)
    {
        try {
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
            if ($requestEvent->hasResponse()) {
                return $this->postProcessResponse($requestEvent->getResponse());
            }

            // load routes and find the route
            $routes = $this->container->get('routes');
            $routesPath = $this->findResource(
                $config->get('snb.routes', '::routes.yml'),
                'config');
            $routes->load($routesPath);

            // Try and find a route that matches the request
            $route = $routes->findMatchingRoute($request);

            // If we have no valid route, then try something else.
            if ($route == null) {
                // No route given. See if we have a 404 route defined?
                $route = $routes->find('404');
                if ($route == null) {
                    // Did not find a page, and no 404 page, so throw an exception
                    throw new PageNotFoundException();
                }
            }

            // send out another event, this time giving systems a chance
            // to change or modify the selected route
            $routeEvent = new RouteEvent($route, $routes);
            $dispatcher->dispatch('kernel.route', $routeEvent);
            if ($routeEvent->hasRoute()) {
                $route = $routeEvent->getRoute();
            }

            // Find info on the controller we'll need to call
            $controllerName = $route->getController();
            $actionName = $route->getAction();
            $args = $route->getArguments();

            // try and create the controller
            $controller = new $controllerName();
            if ($controller instanceof ContainerAware) {
                $controller->setContainer($this->container);
            }

            $response = null;
            if ($controller->init()) {
                // call the action on the controller - if it exists
                if (method_exists($controller, $actionName)) {
                    $response = call_user_func_array(array($controller, $actionName), $args);
                }
            }

            // If we didn't get a response object, send out an event to try and get one
            if (!$response instanceof Response) {
                // Send out an event to try and get a response
                $event = new NoResponseFromControllerEvent($request, $response, $this, $route);
                $dispatcher->dispatch('kernel.missingresponse', $event);

                // Did we get one?
                if ($event->hasResponse()) {
                    $response = $event->getResponse();
                }

                // Do we have a valid response now?
                if (!$response instanceof Response) {
                    // we don't have a valid response from the controller
                    // so throw an exception
                    throw new \LogicException('Controller ('.$controllerName.' -> '.$actionName.') failed to return a response');
                }
            }

            // Return the processed response
            return $this->postProcessResponse($response);
        } catch (\Exception $e) {
            // Send out an exception response event
            return $this->handleExceptionResponse($e, $request);
        }
    }

    /**
     * Sends out a message that allows systems to modify a response prior to it being sent
     * @param  \snb\http\Response $response
     * @return \snb\http\Response
     */
    public function postProcessResponse(Response $response)
    {
        // Send another message out, with the response in it
        // in case anyone wants to modify the response
        $responseEvent = new ResponseEvent($response);
        $this->getDispatcher()->dispatch('kernel.response', $responseEvent);

        return $response;
    }

    /**
     * Sends out an event when an exception if caught handling a request.
     * The idea is that a suitable error response can be generated. If no one
     * creates a good response, then we just re-throw the exception.
     * @param  \Exception         $e
     * @param  \snb\http\Request  $request
     * @return \snb\http\Response
     * @throws \Exception
     */
    public function handleExceptionResponse(\Exception $e, Request $request)
    {
        try {
            // Do we have a dispatcher to hand?
            $dispatcher = $this->getDispatcher();
            if (!$dispatcher) {
                throw $e;
            }

            // Send out an event describing the exception to see if anyone wants
            // to do something about it.
            $event = new ExceptionOnRequestEvent($request, $this, $e);
            $dispatcher->dispatch('kernel.exception', $event);

            // did we get something?
            if (!$event->hasResponse()) {
                // failed to get a response, so re-throw the exception
                throw $e;
            }

            // yay, we got one, return it
            return $this->postProcessResponse($event->getResponse());
        } catch (\Exception $anotherException) {
            // we'll rethrow the original exception
            // But we had better log something first.
            $this->logger->error("Exception thrown in Exception Handler. Rethrowing the original Exception for the default exception handler to process.",
                array('originalException'=> $e, 'newException'=>$anotherException));

            // over to the base ExceptionHandler we go...
            throw $e;
        }
    }
}
