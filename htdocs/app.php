<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

$start = microtime(true);

// Prepare the autoloader
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__ . '/../app/AppKernel.php';
use snb\http\Request;

// try and find the environment and default to dev
$env = isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'dev';

// Create the app kernel and handle the request
$app = new AppKernel($env, $start);
$app->handle(Request::createFromGlobals())->send();
