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
	public function testRouteMatches()
	{
		// create a request
		$request = Request::create('/blog/42/testing-routes');

		// Create a route
		$r = new Route('/blog/{page}/::{section}',
			array('controller'=>'example:DemoController:hello'),
			array('page'=>'int', 'section'=>'slug'),
			array('section'=>'fish'));

		// Test that the route matches the request
		$this->assertTrue($r->isMatch($request));

		// test that the controller is as expected
		$this->assertEquals($r->getController(), 'example\controllers\DemoController');

		// test that the action is a match
		$this->assertEquals($r->getAction(), 'helloAction');

		// test that the arguments are good
		$arguments = $r->getArguments();
		$this->assertTrue(is_array($arguments));
		$this->assertEquals($arguments['page'], 42);
		$this->assertEquals($arguments['section'], 'testing-routes');

		//$url = $r->generate(array('section'=>'a not so <clean> value', 'name'=>'test','page'=>1234));
	}


	public function testRouteNotMatching()
	{
		// create a request
		$request = Request::create('/blog/42/testing-routes');

		// Create a route
		$r = new Route('/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array(), array());

		// Test that the route matches the request
		$this->assertFalse($r->isMatch($request));
	}

	public function testRouteWrongDataType()
	{
		// create a request
		$request = Request::create('/blog/test/42');

		// Create a route
		$r = new Route('/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array('page'=>'int'), array());

		// Test that the route matches the request
		$this->assertTrue($r->isMatch($request));

		$r = new Route('/blog/test/{page}',
			array('controller'=>'example:DemoController:hello'),
			array('page'=>'alpha'), array());

		$this->assertFalse($r->isMatch($request));
	}

}