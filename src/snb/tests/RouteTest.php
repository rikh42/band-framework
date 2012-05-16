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

		// Check various getters
		$this->assertEquals($r->getName(), 'blogtest');
		$this->assertEquals($r->getAction(), 'helloAction');
		$this->assertEquals($r->getController(), 'example\\controllers\\DemoController');
		$this->assertEquals($r->getMethod(), 'GET');
		$this->assertEquals($r->getOption('method'), 'GET');
		$this->assertEquals($r->getOption('madeupcrap'), null);
		$this->assertEquals(
			$r->generate(array('page'=>42, 'section'=>'cars')),
			'/blog/42/cars');
		$this->assertEquals(
			$r->generate(array('page' => 102030)),
			'/blog/102030/fish');

		// Check that the regex for the URL matches what we think it should
		$this->assertEquals($r->getRegex(), '%^/blog/([0-9]+)/(?:([a-zA-Z0-9-]+))?$%u');

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
		$request = Request::create('/blog/42/testing-routes', 'GET');
		$this->assertTrue($r->isMatch('/blog/42/', $request));
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
	 * Testing routes limited by protocol
	 */
	public function testRouteProtocol()
	{
		// create a request
		$http = Request::create('http://example.com/blog/test/42');
		$https = Request::create('https://example.com/blog/test/42');

		// Create a route
		$r = new Route('blogtest', '/blog/test/{page}',
			array('controller' => 'example:DemoController:hello'),
			array(), array());

		// The route had no protocol specified, so it should be happy with http and https
		$this->assertEquals($r->getProtocol(), 'http|https');
		$this->assertTrue($r->isMatch('/blog/test/42', $http));
		$this->assertTrue($r->isMatch('/blog/test/42', $https));

		// Now create a route that only works for http requests
		$r = new Route('blogtest', '/blog/test/{page}',
			array(
				'controller' => 'example:DemoController:hello',
				'protocol' => 'http'
			),
			array(), array());

		// check everything works
		$this->assertEquals($r->getProtocol(), 'http');
		$this->assertTrue($r->isMatch('/blog/test/42', $http));
		$this->assertFalse($r->isMatch('/blog/test/42', $https));

		// Now create a route that only works for https requests
		$r = new Route('blogtest', '/blog/test/{page}',
			array(
				'controller' => 'example:DemoController:hello',
				'protocol' => 'https'
			),
			array(), array());

		// check everything works
		$this->assertEquals($r->getProtocol(), 'https');
		$this->assertFalse($r->isMatch('/blog/test/42', $http));
		$this->assertTrue($r->isMatch('/blog/test/42', $https));
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