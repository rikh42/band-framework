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
     * @param  string             $name
     * @return \snb\http\Response
     */
    public function helloAction($name)
    {
        // render the page using a twig template
        return $this->renderResponse('example:DemoController:hello.twig',
            array('name' => $name));
    }

    /**
     * @return \snb\http\Response
     */
    public function formAction()
    {
        /**
         * @var \snb\form\type\FormType $form
         */

        // build the form
        $formBuilder = $this->getFormBuilder();
        $form = $formBuilder->loadForm('example:DemoController:example.form.yml');

        // Process form submissions
        if ($form->onPostValid($this->getRequest())) {
            $mydata = $form->getData();
            // Save data from the form here...

            // redirect to hello
            return $this->redirectResponse('hello', array('name'=>'Band'));
        }

        // get the data
        $data = array('form' => $form->getView());

        // set up the data for the view and render
        return $this->renderResponse('example:DemoController:form.twig', $data);
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
