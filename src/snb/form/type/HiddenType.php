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

class HiddenType extends FieldType
{

    public function __construct()
    {
        // Do the normal thing
        parent::__construct();

        // Since hidden fields are hidden (duh!), any validation errors
        // they generate should be bubbled up to their parent
        $this->set('bubble_errors', true);
    }

    /**
     * Gets the html type of the field
     * @return string
     */
    public function getType()
    {
        return 'hidden';
    }

    /**
     * Hidden fields can not be edited in the browser
     * @return bool
     */
    public function isEditable()
    {
        return false;
    }

}
