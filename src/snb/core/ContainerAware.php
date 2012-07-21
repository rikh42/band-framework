<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use snb\core\ContainerInterface;

/**
 * ContainerAware
 * Anything derived from this class
 */
class ContainerAware implements ContainerAwareInterface
{
    /**
     * @var snb\core\ContainerInterface
     */
    public $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
