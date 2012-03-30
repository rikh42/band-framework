<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace example\controllers;
use snb\core\Controller;
use snb\http\Response;
use snb\view\TwigView;
use snb\form\FormBuilder;



class DemoController extends Controller
{

	/**
	 * Simple action to handle the home page of the site
	 * @return \snb\http\Response
	 */
	public function indexAction()
	{
		return $this->renderResponse('example:DemoController:index.twig');
	}


	/**
	 * Draws a simple "hello World" style sample page
	 * @param $name
	 * @return \snb\http\Response
	 */
	public function helloAction($name)
	{
		// Prepare some data that will be rendered in the template
		$data = array(
			'name' => $name
		);

		// render it
		return $this->renderResponse('example:DemoController:hello.twig', $data);
	}


	/**
	 * @return \snb\http\Response
	 */
	public function formAction()
	{
		// build the form
		//$form = new FormBuilder('example:DemoController:example.form.yml', $this->container->get('kernel'));
		$formBuilder = new FormBuilder($this->container->get('kernel'));
		$form = $formBuilder->loadForm('example:DemoController:example.form.yml');

		// Process form submissions
		$request = $this->getRequest();
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			if ($form->isValid())
			{
				// redirect to hello
				return $this->redirectResponse('hello', array('name'=>'Band'));
			}
		}

		// get the data
		$data = array('form' => $form->getView());

		// set up the data for the view and render
		try {
			return $this->renderResponse('example:DemoController:form.twig', $data);
		}
		catch (\Exception $e)
		{
			return new Response($e->getMessage());
		}
	}


	/**
	 * @return \snb\http\Response
	 */
	public function Error404Action()
	{
		$r = $this->renderResponse('example:DemoController:404.twig');
		$r->setHTTPResponseCode(404);
		return $r;
	}

}

