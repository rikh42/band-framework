<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use snb\core\DatabaseInterface;
use snb\logger\LoggerInterface;

/**
 * A base class for models that provides some helpful extra functionality
 */
class Model extends ContainerAware
{
    public function __construct()
    {
    }

    /**
     * @return snb\core\DatabaseInterface
     */
    public function getDatabase()
    {
        return $this->container->get('database');
    }

    /**
     * @return snb\logger\LoggerInterface
     */
    public function getLogger()
    {
        return $this->container->get('logger');
    }
}
