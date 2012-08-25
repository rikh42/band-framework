<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


/**
 * Runs a migration command
 * @param $cmd - the command to execute
 * @param $package - the package to execute it on
 * @param $migrate - the migration object
 */
function performMigration($cmd, $package, $migrate)
{
	switch ($cmd)
	{
		case 'up':
			$migrate->up($package);
			break;

		case 'down':
			$migrate->down($package);
			break;

		case 'latest':
			$migrate->latest($package);
			break;

		case 'all':
			$migrate->updateAll();
			break;
	}
}


/**
 * Find the command to execute
 * @param $argc
 * @param $argv
 * @return string
 */
function getCommand($argc, $argv)
{
	if ($argc <= 1)
		return 'all';

	return $argv[$argc-1];
}



/**
 * Find the package to work on
 * @param $argc
 * @param $argv
 * @return string
 */
function getPackage($argc, $argv)
{
	if ($argc == 3)
		return $argv[1];

	return 'app';
}


// Prepare the autoloader
require_once __DIR__.'/autoload.php';
require_once __DIR__.'/AppKernel.php';
use snb\http\Request;

// Create the app kernel and boot the system
$app = new AppKernel('dev', microtime(true));
$app->boot();

// Get the migration manager
$migrate = $app->container->get('db.migrate');

// Decide what migration to perform and do it
$cmd = getCommand($argc, $argv);
$package = getPackage($argc, $argv);
performMigration($cmd, $package, $migrate);

// Show the results
$logger = $app->container->get('logger');
$logger->dump();

