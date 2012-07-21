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

class CheckboxType extends FieldType
{
    /**
     * Gets the html type of the field
     * @return string
     */
    public function getType()
    {
        return 'checkbox';
    }

    /**
     * Called to map the submitted data into the field.
     * Typically this is called when a form is submitted to set up all the fields
     * with the values entered by the user, ready for validation
     * @param $data
     */
    public function bind($data)
    {
        // The value is missing when the checkbox is not checked,
        // or 1 when it is checked.
        $this->set('value', ($data == 1));
    }

    /**
     * Generates a FormView element for this field, copying all the
     * data over to the view in a view-friendly format
     * @return \snb\form\FormView
     */
    public function getView()
    {
        // Create the view
        $view = parent::getView();

        // Find out the value and force it to be true or false
        $value = $this->get('value', false) ? true : false;

        // we want to add the checked attribute if we can
        if ($value) {
            $attr = $this->get('attributes', array());
            $attr['checked'] = 'checked';
            $view->set('attributes', $attr);
        }

        // Always set the value in the checkbox control to 1
        $view->set('value', 1);

        // return it
        return $view;
    }

}
