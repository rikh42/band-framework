<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

/**
 * The interface used to talk to databases
 */
interface DatabaseInterface
{
    public function init();
    public function addConnection($name);
    public function setActiveConnection($name);
    public function isOpenConnection();

    public function all($query, $params = null);
    public function row($query, $params = null);
    public function one($query, $params = null);
    public function query($query, $params = null);

    public function getLastInsertID();
    public function getLastInsertIDString();
}
