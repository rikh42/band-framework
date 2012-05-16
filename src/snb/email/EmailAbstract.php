<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */


namespace snb\email;
use snb\core\ContainerAware;
use snb\core\ConfigInterface;
use snb\logger\LoggerInterface;


/**
 * An generic base class intended to provide the core functionality
 * for that several email services can benefit from
 */
class EmailAbstract extends ContainerAware implements EmailInterface
{
	protected $subject;
	protected $from;
	protected $to;
	protected $cc;
	protected $bcc;
	protected $tag;
	protected $headers;
	protected $htmlBody;
	protected $textBody;
	protected $config;
	protected $logger;



	/**
	 * @param \snb\core\ConfigInterface $config
	 * @param \snb\logger\LoggerInterface $logger
	 */
	public function __construct(ConfigInterface $config, LoggerInterface $logger)
	{
		$this->subject = 'Subject';
		$this->from = '';
		$this->replyTo = '';
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->tag = '';
		$this->headers = array();
		$this->htmlBody = '';
		$this->textBody = '';
		$this->config = $config;
		$this->logger = $logger;
	}




	/**
	 * Sets the subject of the email message
	 * @param $subject
	 * @return EmailAbstract
	 */
	public function subject($subject)
	{
		$this->subject = (string)$subject;
		return $this;
	}



	/**
	 * Adds a "to" address to the message. Each time you call this
	 * you add an extra email address to the "to" list
	 * @param $email - the email address (test@example.com)
	 * @param null $name - optional name of the user (Mr John Smith)
	 * @return EmailAbstract
	 */
	public function to($email, $name = null)
	{
		$this->to[] = $this->prepareEmail($email, $name);
		return $this;
	}



	/**
	 * Adds a cc address to the list.
	 * @param $email
	 * @param null $name
	 * @return EmailAbstract
	 */
	public function cc($email, $name = null)
	{
		$this->cc[] = $this->prepareEmail($email, $name);
		return $this;
	}



	/**
	 * Adds an email address to the Bcc list for the message
	 * @param $email
	 * @param null $name
	 * @return EmailAbstract
	 */
	public function bcc($email, $name = null)
	{
		$this->bcc[] = $this->prepareEmail($email, $name);
		return $this;
	}



	/**
	 * Sets the from address for the message - A message can only be from 1 person
	 * so each call to this just replaces the from address.
	 * @param $email
	 * @param null $name
	 * @return EmailAbstract
	 */
	public function from($email, $name = null)
	{
		$this->from = $this->prepareEmail($email, $name);
		return $this;
	}



	/**
	 * Sets the reply to email address
	 * @param $email
	 * @param null $name
	 * @return EmailAbstract
	 */
	public function replyTo($email, $name = null)
	{
		$this->replyTo = $this->prepareEmail($email, $name);
		return $this;
	}



	/**
	 * Helper function that takes an email address and persons names
	 * and formats them correctly using the "Name <email@domain.com>" format
	 * @param $email
	 * @param $name
	 * @return string
	 */
	protected function prepareEmail($email, $name)
	{
		// if a name is included, build the correct email address
		if ($name != null)
			$email = $name.' <'.$email.'>';

		return $email;
	}




	/**
	 * Sets the HTML message content
	 * @param $html
	 * @return EmailAbstract
	 */
	public function htmlBody($html)
	{
		$this->htmlBody = $html;

		if (empty($this->textBody))
		{
			$this->textBody($this->generatePlainTextFromHtml($html));
		}

		return $this;
	}




	/**
	 * Sets the plain text version of the message
	 * @param $plain
	 * @return EmailAbstract
	 */
	public function textBody($plain)
	{
		$this->textBody = $plain;
		return $this;
	}



	/**
	 * Tries to extract a plain text version of some content from the
	 * html content. Acts as a 'better than nothing' way of getting
	 * some plain text body into your email. This is called automatically
	 * when you set the html body when the plain text body has not been set.
	 * @param $html
	 * @return mixed|string
	 */
	protected function generatePlainTextFromHtml($html)
	{
		// Strip out various headers that provide no content
		$plain = preg_replace('%<head(\s+[^>]*)?>.*</head>%si', '', $html);
		$plain = preg_replace('%<script(\s+[^>]*)?>.*</script>%si', '', $plain);
		$plain = preg_replace('%<style(\s+[^>]*)?>.*</style>%si', '', $plain);

		// Add some spacing as well, between packed together tags
		$plain = preg_replace('%(</[^>]+>)%', ' $1', $plain);

		// strip tags from the rest of the file
		$plain = strip_tags($plain);

		// Trim each line
		$plain = preg_replace('/^[\t ]*/im', '', $plain);
		$plain = preg_replace('/[\t ]+$/m', '', $plain);

		// remove surplus spacing
		$plain = preg_replace('/ {2,}/', ' ', $plain);

		// Remove excessive blank lines
		$plain = preg_replace('/[\r\n]{3,}/', "\n\n", $plain);

		// wrap to a safe width
		$plain = wordwrap($plain, 70, "\n", true);
		return $plain;
	}



	/**
	 * Sets a tag for the message. Not all services use this.
	 * You should set this to the type of message you are sending.
	 * For example, "order", "dispatch", "welcome", "passwordreset" etc
	 * @param $tag
	 * @return EmailAbstract
	 */
	public function tag($tag)
	{
		$this->tag = $tag;
		return $this;
	}




	/**
	 * This is the base class, so this function does nothing here.
	 * Derived classes are expected to provide some real functionality
	 * that results in an email being sent.
	 * @return bool
	 */
	public function send()
	{
		return false;
	}
}

