<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
/* This file based on part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 */

namespace snb\form\type;
use snb\form\type\FieldType;

class TextAreaType extends FieldType
{
    /**
     * Gets the html type of the field
     * @return string
     */
    public function getType()
    {
        return 'textarea';
    }
}
