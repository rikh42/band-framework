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
	function subject($subject);
	function to($email, $name=null);
	function cc($email, $name=null);
	function bcc($email, $name=null);
	function from($email, $name=null);
	function htmlBody($html);
	function textBody($plain);
	function tag($tag);
	function send();
}

