<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\logger;

use snb\logger\HandlerInterface;


class BufferedHandler implements HandlerInterface
{
	protected $level;
	protected $buffer;


	public function __construct($level=0)
	{
		$this->level = (int)$level;
		$this->buffer = array();
	}

	public function handle(array $record)
	{
		// ignore event below our threshold
		if ($record['level'] < $this->level)
			return;

		$this->buffer[] = $record;
	}

	public function dump()
	{
		foreach($this->buffer as $line)
		{
			echo $line['time']->format('Y-m-d H:i:s').' ';
			echo $line['level'].' - ';
			echo $line['message'].'<br>';
			print_r($line['extradata']);
			echo '<br><br>';
		}
	}
}