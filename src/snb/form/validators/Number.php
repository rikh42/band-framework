<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\form\validators;

/**
 * Validate that the field is Not Blank
 */
class Number extends AbstractValidator
{
    const MsgNotANumber = 'notanumber';

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMessage(self::MsgNotANumber, "The value '{{value}}' should be a number.");
    }

    /**
     * @param $value
     * @return bool
     */
    public function isValid($value)
    {
        // clear any old errors
        $this->clearErrors();

        // ints are numbers!
        if (is_int($value)) {
            return true;
        }

        // so are floats!
        if (is_float($value)) {
            return true;
        }

        // strings might be...
        if (is_string($value)) {
            // If the string is digits, or digits.digits, then it's a number
            if (preg_match('/^[-+]?\d+([.,](\d+)?)?$/', $value)) {
                return true;
            }
        }

        // Something else...
        $this->addError(self::MsgNotANumber, array('value'=>$value));

        return false;
    }
}
