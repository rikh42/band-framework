<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;



/**
 * This class creates a context in which the security will happen.
 * Typically this just maps to the name of the session or cookie that is used
 * to back the security. The life setting indicates how long the tokens generated
 * in this context will be valid for, which in turn impacts how long you can be
 * logged in for in this context.
 * Finally, the path setting allows you to limit the context to only parts of the site and
 * is mainly intended to provide a way of limiting the cookie.
 *
 * the recommended way of using this is to create a class that extends this that
 * sets explicit values for these settings, so you can simply go "new BackOfficeContext"
 */
class SecurityContext
{
	public $name;
	public $life;
	public $path;


	/**
	 * @param string $name - name of the context
	 * @param int $life - lifespan (default to 14 days)
	 * @param string $path - path (default to / for whole site)
	 */
	function __construct($name='auth', $life=1209600, $path='/')
	{
		$this->name = $name;
		$this->life = $life;
		$this->path = $path;
	}
}

