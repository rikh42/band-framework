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
use snb\core\ContainerAwareInterface;
use snb\core\ContainerInterface;
use snb\core\KernelInterface;
use snb\logger\LoggerInterface;
use snb\form\type\CsrfType;
use Symfony\Component\Yaml\Yaml;


/**
 * A class to build forms from their yaml description
 */
class FormBuilder extends ContainerAware implements FormBuilderInterface
{
	protected $extensions;
	protected $filters;
	protected $formElements;


	public function __construct()
	{
		// remember these - we'll need them later.
		$this->extensions = array();
		$this->filters = array();
		$this->formElements = array();

		// Add the core extensions
		$this->addExtensions(array(
			'text' => 'snb\form\type\TextType',
			'textarea' => 'snb\form\type\TextAreaType',
			'checkbox' => 'snb\form\type\CheckboxType',
			'radio' => 'snb\form\type\RadioType',
			'choice' => 'snb\form\type\ChoiceType',
			'password' => 'snb\form\type\PasswordType',
			'hidden' => 'snb\form\type\HiddenType',
			'email' => 'snb\form\type\EmailType',
			'date' => 'snb\form\type\DateType',
			'form' => 'snb\form\type\FormType',
			'fieldset' => 'snb\form\type\FormType'	// Alias of form
		));

		// Add the core filters
		$this->addFilters(array(
			'nop' => 'snb\form\filters\NoOpFilter',
			'date' => 'snb\form\filters\DateFilter'
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
	 * Add a list of filter classes to the form builder
	 * @param array $filters
	 */
	public function addFilters(array $filters)
	{
		// Add the filters to the system
		$this->filters = array_merge($this->filters, $filters);
	}


	/**
	 * Adds an element to the form
	 * @param $type
	 * @param $name
	 * @param array $options
	 */
	public function add($type, $name, $options=array())
	{
		// Store the data for this element
		$element = array();
		$element['type'] = $type;
		$element['name'] = $name;
		foreach($options as $key=>$value)
		{
			$element[$key] = $value;
		}

		// add it to the list
		$this->formElements[$name] = $element;
	}



	/**
	 * Returns a form based on the elements added so far
	 * @param string $name
	 * @param array $options
	 * @return \snb\form\type\FormType
	 */
	public function getForm($name='form', $options=array())
	{
		// force the form to be a form :-)
		$options['type'] = 'form';

		// Add the all the children that have been added
		$options['children'] = $this->formElements;

		// reset the form builder, ready for the next form
		$this->formElements = array();

		// create the array in the format we are expecting
		// and generate the form
		$form = array($name => $options);
		return $this->generateForm($form);
	}




	/**
	 * sets up a form using a yaml script
	 * @param $resource - the resource name of the forms yml file
	 * @return \snb\form\type\FormType
	 */
	public function loadForm($resource)
	{
		// Convert the resource name into a filename
		$filename = $this->container->get('kernel')->findResource($resource, 'forms');

		// Read in the content (file or string)
		$content = Yaml::parse($filename);

		// bad data turns into an empty result
		if ($content==null)
			$content = array();

		// must be an array, so trash anything else
		if (!is_array($content))
			$content = array();

		// make the form from the array
		return $this->generateForm($content);
	}



	/**
	 * Support function that takes either the loaded yaml file, or
	 * the internally generated form array, and actually creates the form from it
	 * @param $content
	 * @return \snb\form\type\FormType
	 */
	protected function generateForm($content)
	{
		// We actually are only interested in the first item in the content array
		// We'll use foreach so we can extract the name and data easily
		foreach($content as $name=>$formData)
		{
			// just use the first entry in the file as the form
			$form = $this->loadFormElement($name, $formData);

			// Decide if Csrf protection is enabled or disabled for this form
			// we'll default it to enabled.
			$enableCsrf = $form->get('csrf', true);
			if ($enableCsrf)
			{
				// create a csrf field and add it to the form
				$config = $this->container->get('config');
				$secret = $config->get('snb.csrf.secret', md5($name));
				$csrf = new CsrfType($secret, $name);
				$form->addChild($csrf);
			}

			// return the form
			return $form;
		}

		// not reachable, unless the file is empty
		return null;

	}



	/**
	 * @param string $defaultName
	 * @param array $element
	 * @return \snb\form\type\FormType - or another field type
	 */
	protected function loadFormElement($defaultName, array $element)
	{
		// get the field type
		$type = isset($element['type']) ? $element['type'] : 'text';

		// If the type is unknown, switch to text type
		if (!array_key_exists($type, $this->extensions))
		{
			$this->container->get('logger')->warning('Found unknown field type in form definition. Treating as Text', $type);
			$type = 'text';
		}

		// create the form element
		$class = $this->extensions[$type];
		$form = new $class;
		if ($form instanceof ContainerAwareInterface)
		{
			$form->setContainer($this->container);
		}

		// Give the field a chance to act before any settings are loaded
		$form->beforeLoad();

		// default the name to the name of the element.
		// may be overridden with the name: property
		$form->set('name', $defaultName);

		// Set all its properties and children up
		foreach($element as $key=>$value)
		{
			switch ($key)
			{
				// if the element has children, create them as well
				case 'children':
					foreach($value as $name=>$child)
					{
						$childForm = $this->loadFormElement($name, $child);
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

				case 'filter':
					// See if we know about the filter type (if not, just log and ignore)
					if (array_key_exists($value, $this->filters))
					{
						$filterClass = $this->filters[$value];
						$form->set($key, new $filterClass);
					}
					else
					{
						$this->container->get('logger')->warning('Found unknown filter type in form definition. Ignored.', $value);
					}
					break;


				// everything else just add as a property of the element
				default:
					$form->set($key, $value);
					break;
			}
		}

		$form->afterLoad();
		return $form;
	}


	/**
	 * Converts the validator name into a class name
	 * @param string $name - the name of the validator class
	 * @return string
	 */
	protected function getValidatorClass($name)
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