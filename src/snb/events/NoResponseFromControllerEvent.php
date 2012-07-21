<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\events;
use Symfony\Component\EventDispatcher\Event;
use snb\http\Request;
use snb\routing\Route;
use snb\core\KernelInterface;

/**
 * This event is sent out when a controller does not return a response object
 * If it returns nothing, or a different type of object, that is included inside
 * this event. If you handle this event and generate a response from the data provided
 * then call SetResponse() to hand that back to the kernel
 */
class NoResponseFromControllerEvent extends HasResponseEvent
{
    public $request;
    public $originalResponse;
    public $route;
    public $kernel;

    /**
     * @param \snb\http\Request $request
     * @param $response
     * @param \snb\core\KernelInterface $kernel
     * @param \snb\routing\Route        $route
     */
    public function __construct(Request $request, $response, KernelInterface $kernel, Route $route)
    {
        parent::__construct();
        $this->request = $request;
        $this->route = $route;
        $this->originalResponse = $response;
        $this->kernel = $kernel;
    }
}
