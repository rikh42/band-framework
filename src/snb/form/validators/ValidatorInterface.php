<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\form\validators;

interface ValidatorInterface
{
    public function isValid($value);
    public function setMessage($name, $msg);
    public function getErrors();
    public function clearErrors();
}
