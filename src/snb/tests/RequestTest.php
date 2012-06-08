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

class RequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test that routes Match
	 */
	public function testGeneralRequest()
	{
		// create a request
		$request = Request::create('http://www.example.com/blog/42/testing-routes');

		// Check various getters

		$this->assertEquals($request->getHost(), 'www.example.com');
		$this->assertEquals($request->getHttpHost(), 'http://www.example.com');
		$this->assertFalse($request->isSecure());
		$this->assertEquals($request->getPort(), 80);
		$this->assertEquals($request->getProtocol(), 'http');
		$this->assertEquals($request->getClientIp(), '127.0.0.1');
		$this->assertFalse($request->isAjax());
		$this->assertEquals($request->getPath(), '/blog/42/testing-routes');
		$this->assertEquals($request->getUri(), '/blog/42/testing-routes');
		$this->assertEquals($request->getMethod(), 'GET');
		$this->assertFalse($request->hasSession());
	}


	/**
	 * Test GetPort
	 */
	public function testPort()
	{
		// create a standard request
		$request = Request::create('http://www.example.com/blog/42/testing-routes');
		$this->assertEquals($request->getPort(), 80);

		// create an https request
		$request = Request::create('https://www.example.com/blog/42/testing-routes');
		$this->assertEquals($request->getPort(), 443);
		$this->assertEquals($request->getProtocol(), 'https');

		// create a request that includes a port
		$request = Request::create('https://www.example.com:8080/blog/42/testing-routes');
		$this->assertEquals($request->getPort(), 8080);
		$this->assertEquals($request->getProtocol(), 'https');
		$this->assertEquals($request->getHost(), 'www.example.com');
		$this->assertEquals($request->getHttpHost(), 'https://www.example.com:8080');
	}


	/**
	 * Test accessing the path in various conditions
	 */
	public function testPath()
	{
		$request = Request::create('http://www.example.com/blog/42/testing-routes');
		$this->assertEquals($request->getPath(), '/blog/42/testing-routes');
		$this->assertEquals($request->getUri(), '/blog/42/testing-routes');

		$request = Request::create('http://www.example.com/blog/42/testing-routes', 'GET', array('x'=>42));
		$this->assertEquals($request->getPath(), '/blog/42/testing-routes');
		$this->assertEquals($request->getUri(), '/blog/42/testing-routes?x=42');

		$request = Request::create('http://www.example.com/blog/42/testing-routes', 'GET', array('x'=>42, 'long_name'=>'Lots of data here'));
		$this->assertEquals($request->getPath(), '/blog/42/testing-routes');
		$this->assertEquals($request->getUri(), '/blog/42/testing-routes?x=42&long_name=Lots+of+data+here');

		$request = Request::create('http://www.example.com/blog/42/testing-routes', 'POST', array('x'=>42, 'long_name'=>'Lots of data here'));
		$this->assertEquals($request->getPath(), '/blog/42/testing-routes');
		$this->assertEquals($request->getUri(), '/blog/42/testing-routes');
	}


	/**
	 * test accessing the Get Params
	 */
	public function testGetArguments()
	{
		$request = Request::create('http://www.example.com/blog/42/testing-routes');
		$get = $request->get;
		$this->assertEquals($get->count(), 0);

		$request = Request::create('blog/42', 'GET', array('x'=>42, 'long_name'=>'Lots of data here'));
		$get = $request->get;
		$this->assertEquals($get->count(), 2);
		$this->assertEquals($request->post->count(), 0);
		$this->assertTrue($get->has('x'));
		$this->assertTrue($get->has('long_name'));
		$this->assertFalse($get->has('y'));

		$request = Request::create('blog/42', 'GET', array('x'=>42, 'y'=>'42 text message! £3.40 #trouble'));
		$get = $request->get;
		$x = $get->get('x');
		$this->assertEquals($x, 42);

		// get an int
		$x = $get->getInt('x');
		$this->assertSame($x, 42);
		$this->assertTrue(is_int($x));

		// get as text
		$text = $get->getText('x');
		$this->assertSame($text, '42');
		$this->assertTrue(is_string($text));

		// get only alpha chars
		$text = $get->getAlpha('y');
		$this->assertSame($text, 'textmessagetrouble');
		$this->assertTrue(is_string($text));

		// get only alpha num chars
		$text = $get->getAlphaNum('y');
		$this->assertSame($text, '42textmessage340trouble');

		// get only digit chars
		$text = $get->getDigits('y');
		$this->assertSame($text, '42340');

		// get something that isn't there
		$text = $get->getText('not_there');
		$this->assertSame($text, '');
		$text = $get->getText('not_there', 'default value');
		$this->assertSame($text, 'default value');
		$text = $get->getText('x', 'default value');
		$this->assertSame($text, '42');
	}


	/**
	 * Same again, only for POST arguments
	 */
	public function testPostArguments()
	{
		$request = Request::create('http://www.example.com/blog/42/testing-routes');
		$post = $request->post;
		$this->assertEquals($post->count(), 0);

		$request = Request::create('blog/42', 'POST', array('x'=>42, 'long_name'=>'Lots of data here'));
		$post = $request->post;
		$this->assertEquals($post->count(), 2);
		$this->assertTrue($post->has('x'));
		$this->assertTrue($post->has('long_name'));
		$this->assertFalse($post->has('y'));

		$request = Request::create('blog/42', 'POST', array('x'=>42, 'y'=>'42 text message! £3.40 #trouble'));
		$post = $request->post;
		$x = $post->get('x');
		$this->assertEquals($x, 42);

		// get an int
		$x = $post->getInt('x');
		$this->assertSame($x, 42);
		$this->assertTrue(is_int($x));

		// get as text
		$text = $post->getText('x');
		$this->assertSame($text, '42');
		$this->assertTrue(is_string($text));

		// get only alpha chars
		$text = $post->getAlpha('y');
		$this->assertSame($text, 'textmessagetrouble');
		$this->assertTrue(is_string($text));

		// get only alpha num chars
		$text = $post->getAlphaNum('y');
		$this->assertSame($text, '42textmessage340trouble');

		// get only digit chars
		$text = $post->getDigits('y');
		$this->assertSame($text, '42340');

		// get something that isn't there
		$text = $post->getText('not_there');
		$this->assertSame($text, '');
		$text = $post->getText('not_there', 'default value');
		$this->assertSame($text, 'default value');
		$text = $post->getText('x', 'default value');
		$this->assertSame($text, '42');
	}
}