<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
/* This file based on part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 */

namespace snb\form\type;
use snb\form\type\AbstractType;
use snb\form\FormView;
use snb\http\Request;

/**
 * Represents a form or a subform (subforms are rendered as Fieldsets by default
 */
class FormType extends AbstractType
{
    /**
     * @var array
     */
    public $children;
    protected $boundObject;

    public function __construct()
    {
        // base class init
        parent::__construct();

        // Clear the children array
        $this->children = array();

        // we are not bound to anything yet
        $this->boundObject = null;

        // Force some default values for a form
        $this->set('method', 'POST');
    }

    /**
     * Gets the type of element this is
     * @return string
     */
    public function getType()
    {
        return 'form';
    }

    /**
     * Adds a child form / field to this form
     * @param AbstractType $child
     */
    public function addChild(AbstractType $child)
    {
        $this->children[$child->get('name')] = $child;
        $child->setParent($this);
    }

    /**
     * Removes the child element from the form
     * @param AbstractType $child
     */
    public function removeChild(AbstractType $child)
    {
        $this->removeChildByName($child->get('name'));

    }

    /**
     * Removes the child element from the form
     * @param $name
     */
    public function removeChildByName($name)
    {
        if (array_key_exists($name, $this->children)) {
            $this->children[$name]->setParent(null);
            unset($this->children[$name]);
        }
    }

    /**
     * Searches the form for an element of the given name.
     * @param $name - the name of the field element to look for
     * @return null|AbstractType
     */
    public function findElement($name)
    {
        // Is it me?
        if ($this->getName() == $name) {
            return $this;
        }

        // is it one of my children?
        foreach ($this->children as $child) {
            $el = $child->findElement($name);
            if ($el != null) {
                return $el;
            }
        }

        return null;
    }

    /**
     * Creates the view for this class, then adds its child view to it
     * @return \snb\form\FormView
     */
    public function getView()
    {
        // Build the view
        $view = parent::getView();

        // add the children to it
        foreach ($this->children as $child) {
            $view->addChild($child->getView());
        }

        return $view;
    }

    /**
     * Determine if this class is valid
     * @return bool
     */
    public function isValid()
    {
        // Find out if I am valid
        $valid = parent::isValid();

        // and ask all my children.
        // We always ask everyone, even if the first thing says it isn't valid
        // as this will collect up all the errors in the entire form, so they
        // can all be display, instead of just the first one.
        foreach ($this->children as $child) {
            // if my child isn't valid, then make a note of it
            if (!$child->isValid()) {
                $valid = false;
            }
        }

        // return my state.
        return $valid;
    }

    /**
     * Determine if we have any editable fields in the form
     * @return bool
     */
    public function isEditable()
    {
        // ask all my children if they are editable.
        foreach ($this->children as $child) {
            // if any of my children are editable, I am editable
            if ($child->isEditable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find all the data in the form and return it as an array
     * @return array
     */
    public function getData()
    {
        $data = new \stdClass();
        $this->writeToObject($data);

        return get_object_vars($data);
    }

    /**
     * Associates an object with the form, linking data in the object to fields in the form
     * @param $object - an object with properties that can be bound to the form
     */
    public function bindObject($object)
    {
        // store the object that we are binding to
        $this->boundObject = $object;
        if ($this->boundObject == null) {
            return;
        }

        // read data from the object into the form
        $this->readFromObject($this->boundObject);
    }

    /**
     * Reads data for all the forms children
     * @param $object
     */
    protected function readFromObject($object)
    {
        // The form object has no data, but pull the data into
        // all my child fields.
        foreach ($this->children as $child) {
            $child->readFromObject($object);
        }
    }

    /**
     * Writes all the data from the forms children into the object
     * @param $object
     */
    protected function writeToObject($object)
    {
        // The form object has no data
        // so pass the call on to all my children
        foreach ($this->children as $child) {
            $child->writeToObject($object);
        }
    }

    /**
     * Binds data to all the children of this form
     * @param array $data
     */
    public function bind($data)
    {
        // set the data in all my children
        foreach ($this->children as $child) {
            $name = $child->get('name');
            $value = array_key_exists($name, $data) ? $data[$name] : null;
            $child->bind($value);
        }

        // write the values back to any bound objects
        if ($this->boundObject != null) {
            $this->writeToObject($this->boundObject);
        }
    }

    /**
     * Pulls data from the request and loads it into the forms fields.
     * @param \snb\http\Request $request
     */
    public function bindRequest(Request $request)
    {
        // decide if we should get values from get or post arguments
        switch ($request->getMethod()) {
            default:
            case 'POST':
            case 'PUT':
                $data = $request->post->get($this->get('name'), array());
                break;

            case 'GET':
                $data = $request->get->get($this->get('name'), array());
                break;
        }

        $this->bind($data);
    }

    /**
     * Determines if the form has been successfully submitted
     * if this returns true, you can process the form and redirect.
     * if it returns false, then you need to draw the form.
     * @param  \snb\http\Request $request
     * @return bool
     */
    public function onPostValid(Request $request)
    {
        // If this is a post request
        if ($request->getMethod() == 'POST') {
            // bind the post data to the form
            $this->bindRequest($request);

            // check that the form data is valid
            if ($this->isValid()) {
                return true;
            }
        }

        // not ready to be processed yet
        return false;
    }
}
