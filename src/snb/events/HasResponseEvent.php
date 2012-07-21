<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\events;
use Symfony\Component\EventDispatcher\Event;
use snb\http\Response;

/**
 * This is intended as a base class for events that track responses
 */
class HasResponseEvent extends Event
{
    public $response;

    /**
     * default to null
     */
    public function __construct()
    {
        $this->response = null;
    }

    /**
     * Determine if the event has a response in it yet?
     * @return bool
     */
    public function hasResponse()
    {
        return ($this->response != null);
    }

    /**
     * Returns the response
     * @return snb\http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the response on the event
     * @param \snb\http\Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
