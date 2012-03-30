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

// Create the app kernel and handle the request
$app = new AppKernel('dev', $start);
$app->handle(Request::createFromGlobals())->send();

// Debug to show some timings.
$end = microtime(true);
$t = (int) (($end - $start) * 1000);
echo "<br>$t";

