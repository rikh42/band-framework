<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use \snb\http\Request;

interface KernelInterface
{
    public function boot();
    public function getPackagePath($name);
    public function findResource($name, $type);
    public function handle(Request $request);

    /**
     * @param string $name - the name of the service to add
     * @param $ref - it's classname or an instance of the service
     * @return ServiceDefinition
     */
    public function addService($name, $ref);
    public function addModel($name, $ref);
    public function getContainer();
}
