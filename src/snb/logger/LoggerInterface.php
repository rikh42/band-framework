<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\logger;


interface LoggerInterface
{
	// for debugging use
	function debug($message, $extraData = null);

	// useful to know (eg, logged in as Bob, creating a new user etc)
	function info($message, $extraData = null);

	// Something went wrong, but we can recover
	function warning($message, $extraData = null);

	// Something went wrong, and we'll have to tell the user
	function error($message, $extraData = null);

	// Makes a note of the current and peak memory usage at the moment the call was made
	function logMemory($message);

	// logs and SQL query
	function logQuery($message, $sql, $args, $queryTime);

	// Logs the current time
	function logTime($message);

	/*
	 * Should we also have the following....
	 * logMemory($msg)
	 * logQuery($sql, $args, $querytime)
	 * logTime($msg)
	 * logEmail($msgbody)
	 *
	 * Also display include file list at the end
	 *
	 * All these things should be treated as debug level events,
	 * apart from failed SQL queries, which should be promoted to error level events
	 *
	 * Also, should all the other logging functions take a mixed
	 * instead of an array. That way you can pass in any old data
	 * and have it rendered to some panel at the end
	 */
}
