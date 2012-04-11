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



namespace snb\form;



/**
 * A class to represent a form element (form, fieldset, field etc) in a form
 * It supports a bunch of iterators so Twig can more easily access the children
 * of this class.
 */
class FormView implements \ArrayAccess, \IteratorAggregate, \Countable
{
	protected $vars = array();
	protected $parent;
	protected $children;


	/**
	 * Set up the FormView
	 */
	public function __construct()
	{
		// reset the children and parent settings
		$this->children = array();
		$this->parent = null;

		// Add myself to the vars, which makes iteration simpler.
		$this->set('form', $this);
	}


	/**
	 * Adds a new FormView element as a child of this one
	 * @param FormView $child
	 */
	public function addChild(FormView $child)
	{
		$child->setParent($this);
		$this->children[] = $child;
	}


	/**
	 * Sets the parent form for this element (FormView is used to
	 * represent each and every element in a form)
	 * @param FormView $parent
	 */
	public function setParent(FormView $parent)
	{
		$this->parent = $parent;
	}


	/**
	 * Sets the named property
	 * @param string $name
	 * @param $value
	 */
	public function set($name, $value)
	{
		$this->vars[(string)$name] = $value;
	}



	/**
	 * gets the named property
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		$name = (string) $name;
		if ($this->has($name))
		{
			return $this->vars[$name];
		}

		return $default;
	}


	/**
	 * Determine if the named property exists
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return array_key_exists((string)$name, $this->vars);
	}


	/**
	 * Gets all the vars in the view
	 * @return array
	 */
	public function all()
	{
		return $this->vars;
	}


	/**
	 * Provide a get method so that Twig can access the vars store
	 * eg. form.properties.whatever
	 * @return array
	 */
	public function getProperties()
	{
		return $this->vars;
	}






	/**
	 * Returns a child by name (implements \ArrayAccess).
	 *
	 * @param string $name The child name
	 *
	 * @return FormView The child view
	 */
	public function offsetGet($name)
	{
		return $this->children[$name];
	}

	/**
	 * Returns whether the given child exists (implements \ArrayAccess).
	 *
	 * @param string $name The child name
	 *
	 * @return Boolean Whether the child view exists
	 */
	public function offsetExists($name)
	{
		return isset($this->children[$name]);
	}

	/**
	 * Implements \ArrayAccess.
	 *
	 * @throws \BadMethodCallException always as setting a child by name is not allowed
	 */
	public function offsetSet($name, $value)
	{
		throw new \BadMethodCallException('Not supported');
	}

	/**
	 * Removes a child (implements \ArrayAccess).
	 *
	 * @param string $name The child name
	 */
	public function offsetUnset($name)
	{
		unset($this->children[$name]);
	}

	/**
	 * Returns an iterator to iterate over children (implements \IteratorAggregate)
	 *
	 * @return \ArrayIterator The iterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->children);
	}

	/**
	 * Implements \Countable.
	 *
	 * @return integer The number of children views
	 */
	public function count()
	{
		return count($this->children);
	}

}

