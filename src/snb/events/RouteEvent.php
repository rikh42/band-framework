<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\events;
use Symfony\Component\EventDispatcher\Event;
use snb\Routing\Route;
use snb\routing\RouteCollection;

/**
 * An event that gets sent around once a matching route has been found
 * This gives you a chance to change the route, should you want to
 */
class RouteEvent extends Event
{
    public $route;
    public $allRoutes;
    protected $replacementRoute;

    /**
     * @param \snb\Routing\Route           $route
     * @param \snb\Routing\RouteCollection $allRoutes
     */
    public function __construct(Route $route, RouteCollection $allRoutes)
    {
        $this->route = $route;
        $this->allRoutes = $allRoutes;
        $this->replacementRoute = null;
    }

    /**
     * Determine if the event has a new Route to use
     * @return bool
     */
    public function hasRoute()
    {
        return ($this->replacementRoute != null);
    }

    /**
     * @return \snb\Routing\Route
     */
    public function getRoute()
    {
        return $this->replacementRoute;
    }

    /**
     * Sets the replacement route to use
     * @param \snb\Routing\Route $route
     */
    public function setRoute(Route $route)
    {
        // remember the new route
        $this->replacementRoute = $route;

        // Stop the propagation of the event now that a replacement has been found
        $this->stopPropagation();
    }
}
