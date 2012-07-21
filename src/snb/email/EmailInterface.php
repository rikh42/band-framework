<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;

interface EmailInterface
{
    public function subject($subject);
    public function to($email, $name=null);
    public function cc($email, $name=null);
    public function bcc($email, $name=null);
    public function from($email, $name=null);
    public function htmlBody($html);
    public function textBody($plain);
    public function tag($tag);
    public function send();

    public function getHtmlBody();
    public function getTextBody();
}
