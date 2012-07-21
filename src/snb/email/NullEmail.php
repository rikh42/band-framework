<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;

// Implements the Email interface, but actually does nothing.
// ideal for development when you don't actually want any emails to be sent
class NullEmail implements EmailInterface
{
    public function subject($subject)		{ return $this; }
    public function to($email, $name=null)	{ return $this; }
    public function cc($email, $name=null)	{ return $this; }
    public function bcc($email, $name=null)	{ return $this; }
    public function from($email, $name=null){ return $this;}
    public function htmlBody($html)			{ return $this;}
    public function textBody($plain)		{ return $this;}
    public function tag($tag) 				{ return $this;}
    public function send() 					{}
    public function getHtmlBody()           { return ''; }
    public function getTextBody()           { return ''; }

}
