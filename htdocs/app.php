<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

// Prepare the autoloader
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__ . '/../app/AppKernel.php';
use snb\http\Request;

// Work out the environment. this is set in Apache to dev or live via the rewrite rule.
// eg. RewriteRule ^(.*)$ app.php [QSA,E=BAND_ENV:dev,L]
// or directly: SetEnv BAND_ENV dev
$env = 'dev';
if (isset($_SERVER['BAND_ENV'])) {
    $env = $_SERVER['BAND_ENV'];
} else {
    if (isset($_SERVER['REDIRECT_BAND_ENV'])) {
        $env = $_SERVER['REDIRECT_BAND_ENV'];
    }
}

// Create the app kernel and handle the request
$app = new AppKernel($env);
$app->handle(Request::createFromGlobals())->send();
