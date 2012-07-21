<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

/**
 * ContainerInterface
 * The interface that Container Classes must provide
 */
interface ContainerInterface
{
    public function set($name, $object);
    public function setMany($list);
    public function get($name);
    public function getMatching($name);
}
