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
use snb\core\KernelInterface;

/**
 * This event is sent out when an exception is thrown while handling a request.
 * Ideally you would handle this event to generate a suitable error page
 */
class ExceptionOnRequestEvent extends HasResponseEvent
{
    public $request;
    public $exception;
    public $kernel;

    /**
     * @param \snb\http\Request $request
     * @param \Exception        $e
     */
    public function __construct(Request $request, KernelInterface $kernel, \Exception $e)
    {
        parent::__construct();
        $this->request = $request;
        $this->exception = $e;
        $this->kernel = $kernel;
    }
}
