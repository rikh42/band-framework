<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

require_once __DIR__ . '/../src/snb/core/AutoLoader.php';
use snb\core\AutoLoader;

// Set up the autoloader
$loader = new AutoLoader();
$loader->registerNamespaces(array(
	'snb' => __DIR__.'/../src',
	'Symfony' => __DIR__.'/../src',
	'example' => __DIR__.'/../src',
));
$loader->registerPrefixes(array(
	'Twig_'            => __DIR__.'/../src/Symfony/Component/Twig/lib'
));
$loader->register();
