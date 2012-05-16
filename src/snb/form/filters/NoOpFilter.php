<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\form\filters;
use snb\form\filters\FilterInterface;

/**
 * A simple filter that does nothing (input = output)
 */
class NoOpFilter implements FilterInterface
{
	function in($value)
	{
		return $value;
	}

	function out($value)
	{
		return $value;
	}
}
