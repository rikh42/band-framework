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

use snb\form\FormView;



/**
 * Base class for all Form related elements, including forms
 */
class AbstractType
{
	/**
	 * @var array
	 */
	protected $properties;


	/**
	 * @var array
	 */
	protected $errors;

	/**
	 * @var array
	 */
	protected $validators;

	/**
	 * @var AbstractType
	 */
	protected $parent;



	/**
	 * ctor
	 */
	public function __construct()
	{
		$this->properties = array();
		$this->errors = array();
		$this->validators = array();

		// it is important that all elements have a name property
		$this->set('name', 'none');
	}


	/**
	 * Called just before the data is loaded into the field
	 * Lets you set any defaults etc
	 */
	public function beforeLoad()
	{
	}


	/**
	 * Called just after the element has been loaded / set up
	 * allowing you to modify any data or perform any post processing you
	 * need prior to the form being used.
	 */
	public function afterLoad()
	{
	}



	/**
	 * Find the name of the property that we are binding data in this field to.
	 * By default it will be the same as the name of the field, but it can be
	 * overridden by setting the 'binding' property on the field
	 * @return string
	 */
	public function getObjectBinding()
	{
		if ($this->has('binding'))
		{
			return $this->get('binding');
		}

		// no specific binding was set, so try and
		// bind to a property with the same name as the field
		return $this->get('name');
	}



	/**
	 * Reads a property from an object
	 * We look for a getter, an isser (eg IsNewUser()) or a public property
	 * @param $object - the object we are reading data from
	 * @param $property - the name of the property to read
	 * @param $default - a default value to use when we can't find the property
	 * @return mixed - the value of the property
	 */
	protected function readProperty($object, $property, $default)
	{
		// try and find a value from the object
		if (is_object($object))
		{
			// look for the properties of object
			$reflection = new \ReflectionClass($object);
			$get = 'get'.$property;
			$is = 'is'.$property;

			if ($reflection->hasMethod($get))
			{
				if ($reflection->getMethod($get)->isPublic())
				{
					// call the getter to read the value (eg $object->getUsername())
					return $object->$get();
				}
			}
			else if ($reflection->hasMethod($is))
			{
				if ($reflection->getMethod($is)->isPublic())
				{
					// call the getter to read the value (eg $object->isNewUser())
					return $object->$is();
				}
			}
			else if ($reflection->hasProperty($property))
			{
				if ($reflection->getProperty($property)->isPublic())
				{
					// The object has a public property of the right name, so just use that
					return $object->$property;
				}
			}
			elseif (property_exists($object, $property))
			{
				// need this test to work with stdClass objects
				return $object->$property;
			}
		}

		return $default;
	}



	/**
	 * Writes a value into the appropriate property of the object
	 * We look for a setter function, or a public property to write to
	 * @param $object - the object we are writing to
	 * @param $property - the name of the property to write to
	 * @param $value - the value to write
	 */
	protected function writeProperty($object, $property, $value)
	{
		// try and find a value from the object
		if (is_object($object))
		{
			// look for the properties of object
			$reflection = new \ReflectionClass($object);
			$set = 'set'.$property;

			if ($reflection->hasMethod($set))
			{
				if ($reflection->getMethod($set)->isPublic())
				{
					// call the getter to read the value (eg $object->getUsername())
					$object->$set($value);
				}
			}
			else if ($reflection->hasProperty($property))
			{
				if ($reflection->getProperty($property)->isPublic())
				{
					// The object has a public property of the right name, so just use that
					$object->$property = $value;
				}
			}
			else if (property_exists($object, $property))
			{
				// need this test to work with stdClass objects
				$object->$property = $value;
			}
			else if ($reflection->getShortName() == 'stdClass')
			{
				// if it really is a stdClass object, just allow writing to it.
				$object->$property = $value;
			}
		}
	}


	/**
	 * Updates the field to use the value from the object
	 * @param $object
	 */
	protected function readFromObject($object)
	{
		// Find the name of the property we need to look for
		$property = $this->getObjectBinding();

		// default the value to be the current value
		$value = $this->readProperty($object, $property, $this->get('value'));
		$this->set('value', $value);
	}



	/**
	 * Updates the object to use the latest value from the field
	 * @param $object
	 */
	protected function writeToObject($object)
	{
		// Find the name of the property we need to look for
		$property = $this->getObjectBinding();
		$this->writeProperty($object, $property, $this->get('value'));
	}



	/**
	 * Gets the html type of the field
	 * @return string
	 */
	public function getType()
	{
		// This should always be overriden
		return 'abstract';
	}


	/**
	 * Sets the fields parent
	 * @param $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}


	/**
	 * Gets the name of the field.
	 * @return mixed
	 */
	public function getName()
	{
		return $this->get('name');
	}


	/**
	 * Gets the full name of the field. this is the name used
	 * when rendering the form, and is generally looks a bit like
	 * this: "form_name[field_name]"
	 * We do this to ensure that all the values in a form come through to PHP
	 * in a single $_POST value, as an array that matches the structure of the form
	 * @return string
	 */
	public function getFullName()
	{
		if ($this->parent == null)
			return $this->getName();

		$fullName = $this->parent->getFullname();
		$fullName .= '['.$this->getName().']';
		return $fullName;
	}



	/**
	 * Generates the the elements id from it's name and its parents name
	 * @return string - the id for this form element
	 */
	public function getId()
	{
		// If we don't have a parent, then my id is my name
		if ($this->parent == null)
			return $this->getName();

		// if I am a child, my id is ParentName-MyName
		return $this->parent->getId().'-'.$this->getName();
	}



	/**
	 * Called to map the submitted data into the field.
	 * Typically this is called when a form is submitted to set up all the fields
	 * with the values entered by the user, ready for validation
	 * @param $data
	 */
	public function bind($data)
	{
		$this->set('value', $data);
	}


	/**
	 * Generates a FormView element for this field, copying all the
	 * data over to the view in a view-friendly format
	 * @return \snb\form\FormView
	 */
	public function getView()
	{
		// Create the view
		$view = new FormView();

		// Copy over any properties into the view
		foreach($this->properties as $name=>$value)
		{
			$view->set($name, $value);
		}

		// Custom values that are generated or outside the standard properties
		$view->set('id', $this->getId());
		$view->set('type', $this->getType());
		$view->set('full_name', $this->getFullName());
		$view->set('errors', $this->errors);
		return $view;
	}




	/**********************************
	 * Properties
	 */

	/**
	 * Sets the named value
	 * @param string $name
	 * @param $value
	 */
	public function set($name, $value)
	{
		$this->properties[(string)$name] = $value;
	}



	/**
	 * gets the named value
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		$name = (string)$name;
		if ($this->has($name))
			return $this->properties[$name];

		return $default;
	}



	/**
	 * @param string $name
	 * @return bool - true if the named value exists, false if not
	 */
	public function has($name)
	{
		return array_key_exists((string)$name, $this->properties);
	}



	/***********************************
	 * Validators
	 */

	/**
	 * Adds a validator to the form field
	 * @param $validator
	 */
	public function addValidator($validator)
	{
		$this->validators[] = $validator;
	}


	/**
	 * Removes all validators from the field
	 */
	public function clearValidators()
	{
		$this->validators = array();
	}



	/**
	 * Test if the current properties (mostly the value property)
	 * appears to be valid, according to any validators we have registered
	 * with the form field.
	 * @return bool
	 */
	public function isValid()
	{
		// hope that all is well.
		$valid = true;

		// iterate over the validators on the field
		foreach($this->validators as $validator)
		{
			// if any are not valid, make a note of it
			// we hope that the validators will set an appropriate error
			// on us, so that it can be rendered in the view.
			if (!$validator->isValid($this->get('value')))
			{
				$valid = false;
				foreach($validator->getErrors() as $err)
				{
					$this->addError($err);
				}
			}

		}

		return $valid;
	}


	/**
	 * Adds an error message to the field
	 * This is normally called by one of the validators
	 * @param string $msg
	 */
	public function addError($msg)
	{
		// Does this field bubble its error messages up to its parent?
		$bubbleErrors = $this->get('bubble_errors', false);
		if (!$bubbleErrors)
		{
			// nope, so store them here
			$this->errors[] = $msg;
			return;
		}

		// if we have a parent, push the error up there
		if ($this->parent != null)
			$this->parent->addError($msg);
	}
}

