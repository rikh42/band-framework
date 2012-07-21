<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\events;
use Symfony\Component\EventDispatcher\Event;
use snb\core\KernelInterface;
use snb\http\Request;

/**
 * An event that gets sent around before the controller is invoked.
 * Really designed to help with firewall support
 */
class RequestEvent extends HasResponseEvent
{
    public $kernel;
    public $request;

    /**
     * @param \snb\core\KernelInterface $kernel
     * @param \snb\http\Request         $request
     */
    public function __construct(KernelInterface $kernel, Request $request)
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->request = $request;
    }
}
