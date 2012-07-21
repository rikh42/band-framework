<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;
use snb\email\EmailAbstract;

/**
 * Sends an email using the PHP mail function
 */
class Mail extends EmailAbstract
{
    public function send()
    {
        // prepare the message here, and send it
        $to = implode(', ', $this->to);

        // Create a bit of text to act as the boundary between the different parts
        $boundary = 'Multipart_Boundary_x'.md5(time()).'x';

        // prepare the headers
        $headers = empty($this->from) ? '' : 'From: '.$this->from. "\r\n";
        $headers .= empty($this->replyTo) ? '' : 'Reply-To: '.$this->replyTo."\r\n";
        $headers .= empty($this->cc) ? '' : 'CC: '.implode(', ', $this->cc)."\r\n";
        $headers .= empty($this->bcc) ? '' : 'BCC: '.implode(', ', $this->bcc)."\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $headers .= "Content-Transfer-Encoding: 7bit\r\n";

        // Start the mail body
        $body = "This is a multi-part message in mime format.\n\n";

        // Start with the plain text version
        $body.= "--$boundary\n";
        $body.= "Content-Type: text/plain; charset=\"charset=us-ascii\"\n";
        $body.= "Content-Transfer-Encoding: 7bit\n\n";
        $body.= $this->textBody;
        $body.= "\n\n";

        // then add the HTML version
        $body.= "--$boundary\n";
        $body.= "Content-Type: text/html; charset=\"UTF-8\"\n";
        $body.= "Content-Transfer-Encoding: 7bit\n\n";
        $body.= $this->htmlBody;
        $body.= "\n\n";

        // mark the end of the message
        $body.= "--$boundary--\n";

        // Send the message
        mail($to, $this->subject, $body, $headers);
    }
}
