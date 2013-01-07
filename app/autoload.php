<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

require_once __DIR__ . '/../src/snb/core/AutoLoaderContainer.php';
require_once __DIR__ . '/../src/snb/core/AutoLoaderInterface.php';
require_once __DIR__ . '/../src/snb/core/AutoLoader.php';
use snb\core\AutoLoadContainer;
use snb\core\AutoLoader;

// Create a suitable Auto Loader and register it with PHP
AutoLoadContainer::register(new AutoLoader());

// Add the PSR-0 namespaces to the AutoLoader
AutoLoadContainer::addNamespaces(array(
    'snb' => __DIR__.'/../src',
    'Symfony' => __DIR__.'/../src',
    'example' => __DIR__.'/../src'
));

// Add the PSR-0 Prefix class to the AutoLoader
AutoLoadContainer::addPrefixes(array(
    'Twig_'            => __DIR__.'/../src/Symfony/Component/Twig/lib'
));

