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
 * An event that gets sent around before the controller is invoked.
 * Really designed to help with firewall support
 */
class ResponseEvent extends Event
{
    public $response;

    /**
     * @param \snb\http\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the response
     * @return snb\http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
