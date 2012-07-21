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
class Digits extends AbstractValidator
{
    const MsgNotDigits = 'notdigits';

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMessage(self::MsgNotDigits, "The value '{{value}}' must contain only digits.");
    }

    /**
     * @param $value
     * @return bool
     */
    public function isValid($value)
    {
        // clear any old errors
        $this->clearErrors();

        // ints are digits
        if (is_int($value)) {
            return true;
        }

        if (is_string($value)) {
            // If the string is all digits, then it's digits
            if (preg_match('/^\d+$/', $value)) {
                return true;
            }
        }

        // Something else...
        $this->addError(self::MsgNotDigits, array('value'=>$value));

        return false;
    }
}
