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
use snb\http\Response;



/**
 * An event that gets sent around before the controller is invoked.
 * Really designed to help with firewall support
 */
class RequestEvent extends Event
{
	public $kernel;
	public $request;
	public $response;


	/**
	 * @param \snb\core\KernelInterface $kernel
	 * @param \snb\http\Request $request
	 */
	public function __construct(KernelInterface $kernel, Request $request)
	{
		$this->kernel = $kernel;
		$this->request = $request;
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
