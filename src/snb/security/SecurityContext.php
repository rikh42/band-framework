<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\security;



//==============================
// SecurityContext
// Some data used by the sign in manager - this really should be defined somewhere else
// as it is specific to a region of the site.
//==============================
class SecurityContext
{
	public $name;
	public $life;
	public $path;


	function __construct($name, $life, $path)
	{
		$this->name = $name;
		$this->life = $life;
		$this->path = $path;
	}
}

