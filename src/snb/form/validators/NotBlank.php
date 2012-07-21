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
class NotBlank extends AbstractValidator
{
    const MsgNotBlank = 'notblank';

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMessage(self::MsgNotBlank, 'This is a required field and can not be left blank');
    }

    /**
     * It is Not Blank if the field contains anything
     * @param $value
     * @return bool
     */
    public function isValid($value)
    {
        // clear any old errors
        $this->clearErrors();

        // Null == blank, empty string == blank
        if (($value === null) || ((is_string($value) && $value===''))) {
            $this->addError(self::MsgNotBlank);

            return false;
        }

        // I guess there is something there, so it isn't blank
        return true;
    }
}
