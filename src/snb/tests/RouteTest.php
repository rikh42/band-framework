<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\Tests;

use snb\routing\Route;
use snb\http\Request;

class RouteTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test that routes Match
	 */
	public function testRouteMatches()
	{
		// create a request
		$request = Request::create('/blog/42/testing-routes');

		// Create a route
		$r = new Route('blogtest', '/blog/{page}/::{section}',
			array('controller'=>'example:DemoController:hello', 'method'=>'GET'),
			array('page'=>'int', 'section'=>'slug'),
			array('section'=>'fish'));

		// Test that the route matches the request
		$this->assertTrue($r->isMatch('/blog/42/testing-routes', $request));

		// test that the controller is as expected
		$this->assertEquals($r->getController(), 'example\controllers\DemoController');

		// test that the action is a match
		$this->assertEquals($r->getAction(), 'helloAction');

		// test that the arguments are good
		$arguments = $r->getArguments();
		$this->assertTrue(is_array($arguments));
		$this->assertEquals($arguments['page'], 42);
		$this->assertEquals($arguments['section'], 'testing-routes');

		// Test that optional arguments are correctly handled
		$this->assertTrue($r->isMatch('/blog/42/', $request));
		$arguments = $r->getArguments();
		$this->assertEquals($arguments['section'], 'fish');

		// Check a POST request against a routes with a method
		$request = Request::create('/blog/42/testing-routes', 'POST');
		$this->assertFalse($r->isMatch('/blog/42/', $request));
	}



	/**
	 * Testing routes don't match
	 */
	public function testRouteNotMatching()
	{
		// create a request
		$request = Request::create('/blog/42/testing-routes');

		// Create a route
		$r = new Route('blogtest', '/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array(), array());

		// Test that the route matches the request
		$this->assertFalse($r->isMatch('/blog/42/testing-routes', $request));
	}



	/**
	 * Testing the same URL with different data types for the placeholders
	 */
	public function testRouteWrongDataType()
	{
		// create a request
		$request = Request::create('/blog/test/42');

		// Create a route
		$r = new Route('blogtest', '/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array('page'=>'int'), array());

		// Test that the route matches the request
		$this->assertTrue($r->isMatch('/blog/test/42', $request));

		$r = new Route('another', '/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array('page'=>'alpha'), array());

		$this->assertFalse($r->isMatch('/blog/test/42', $request));
	}

}