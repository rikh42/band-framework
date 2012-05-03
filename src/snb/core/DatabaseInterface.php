<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\core;


/**
 * The interface used to talk to databases
 */
interface DatabaseInterface
{
	function init();
	function addConnection($name);
	function setActiveConnection($name);
	function isOpenConnection();


	function all($query, $params = null);
	function row($query, $params = null);
	function one($query, $params = null);
	function query($query, $params = null);


	function getLastInsertID();
	function getLastInsertIDString();
}

