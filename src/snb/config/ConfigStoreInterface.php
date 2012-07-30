<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

interface ConfigStoreInterface
{
    /**
     * @abstract
     * @return array
     * Returns an array of settings and their values
     */
    public function getSettings();
}
