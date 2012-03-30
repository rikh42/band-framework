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
use snb\core\ContainerAware;
use snb\core\ContainerInterface;
use snb\core\KernelInterface;
use snb\logger\LoggerInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * A class to build forms from their yaml description
 */
class FormBuilder extends ContainerAware
{
	protected $kernel;
	protected $logger;
	protected $extensions;
	protected $form;


	/**
	 * @param \snb\core\KernelInterface $kernel
	 * @param null|\snb\logger\LoggerInterface $logger
	 */
	public function __construct(KernelInterface $kernel, LoggerInterface $logger=null)
	{
		// remember these - we'll need them later.
		$this->kernel = $kernel;
		$this->logger = $logger;
		$this->extensions = array();

		// Add the core extensions
		$this->addExtensions(array(
			'text' => 'snb\form\type\TextType',
			'password' => 'snb\form\type\PasswordType',
			'form' => 'snb\form\type\FormType',
			'fieldset' => 'snb\form\type\FormType'	// Alias of form
		));
	}


	/**
	 * Add a list of field type extensions to the form builder.
	 * @param array $extensions
	 */
	public function addExtensions(array $extensions)
	{
		// add the new extensions to the list
		$this->extensions = array_merge($this->extensions, $extensions);
	}



	/**
	 * sets up a form using a yaml script
	 * @param $resource - the resource name of the forms yml file
	 * @return snb\form\type\FormType
	 */
	public function loadForm($resource)
	{
		// Convert the resource name into a filename
		$filename = $this->kernel->findResource($resource, 'forms');

		// Read in the content (file or string)
		$content = Yaml::parse($filename);

		// bad data turns into an empty result
		if ($content==null)
			$content = array();

		// must be an array, so trash anything else
		if (!is_array($content))
			$content = array();

		// check that their is a form
		if (!isset($content['form']))
			$content['form'] = array();

		// Finally, create the form from the topmost element
		// This will create the form and all its children
		return $this->loadFormElement($content['form']);
	}



	/**
	 * @param array $element
	 * @return null
	 */
	public function loadFormElement(array $element)
	{
		// get the field type
		$type = isset($element['type']) ? $element['type'] : 'text';

		// If the type is unknown, fail
		if (!array_key_exists($type, $this->extensions))
			return null;

		// create the form element
		$class = $this->extensions[$type];
		$form = new $class;

		// Set all its properties and children up
		foreach($element as $key=>$value)
		{
			switch ($key)
			{
				// if the element has children, create them as well
				case 'children':
					foreach($value as $child)
					{
						$childForm = $this->loadFormElement($child);
						$form->addChild($childForm);
					}
					break;

				// If the element has validators, load and process them
				case 'validators':
					foreach ($value as $validator=>$options)
					{
						$class = $this->getValidatorClass($validator);
						$v = new $class($options);
						$form->addValidator($v);
					}
					break;

				// everything else just add as a property of the element
				default:
					$form->set($key, $value);
					break;
			}
		}

		return $form;
	}


	/**
	 * Converts the validator name into a class name
	 * @param string $name - the name of the validator class
	 * @return string
	 */
	public function getValidatorClass($name)
	{
		if (strpos($name, '\\')===false)
		{
			// does not look like a full class name,
			// so try and find the class in the standard location
			return '\\snb\\form\\validators\\'.$name;
		}

		// assume that the name is a full class name, so just use it
		return $name;
	}
}