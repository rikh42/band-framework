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
use snb\form\type\FieldType;
use snb\form\FormView;

class ChoiceType extends FieldType
{
	/**
	 * Gets the html type of the field
	 * @return string
	 */
	public function getType()
	{
		return 'choice';
	}


	/**
	 * Adjust the standard full name, depending on the state of multiselect and expanded
	 * @return string
	 */
	public function getFullName()
	{
		// get the normal name
		$fullName = parent::getFullName();

		// if the control is a multiselect, non expanded control, then we need to add something
		$multiselect = $this->get('multiselect', false);
		$expanded = $this->get('expanded', false);
		if (($multiselect) && (!$expanded))
			$fullName .= '[]';

		return $fullName;
	}



	/**
	 * Called to map the submitted data into the field.
	 * Typically this is called when a form is submitted to set up all the fields
	 * with the values entered by the user, ready for validation
	 * @param $data
	 */
	public function bind($data)
	{
		// when multi-select items are set, we sometimes need to remap the data a little
		if (is_array($data))
		{
			// remap the data
			$data = array_values($data);
		}

		// finally, set the value like normal
		parent::bind($data);
	}



	/**
	 * Build the view, which may consist of child elements
	 * @return \snb\form\FormView
	 */
	public function getView()
	{
		// Build the view
		$view = parent::getView();

		// If this set of choices is meant to be expanded,
		// then generate the child controls.
		$multiselect = $this->get('multiselect', false);
		if ($this->get('expanded', false))
		{
			// yes, this is an expanded control
			$choices = $this->get('choices', array());

			// Get the value - make sure it is an array, as that simplifies the code below
			$value = $this->get('value');
			if (!is_array($value))
				$value = array($value);

			$checkedItemCount = 0;
			$firstChild = null;
			foreach ($choices as $key=>$title)
			{
				// make a checkbox for each item please.
				$child = new FormView();
				if ($firstChild==null)
					$firstChild = $child;

				// We only copy over / set up a limited set of properties
				// eg. if we copied "hint", every expanded item would have
				// the same hint next to it
				$child->set('id', $this->getId().'-'.$key);
				$child->set('label', $title);
				$child->set('value', $key);
				$child->set('name', $key);
				$child->set('attributes', $this->get('attributes'));

				// Decide if we want checkboxes or radio buttons
				if ($multiselect)
				{
					$child->set('type', 'checkbox');
					$child->set('full_name', $this->getFullName().'['.$key.']');
				}
				else
				{
					$child->set('type', 'radio');
					$child->set('full_name', $this->getFullName());
				}

				// See if this item is checked or not
				if (in_array($key, $value))
				{
					$attr = $this->get('attributes', array());
					$attr['checked'] = 'checked';
					$child->set('attributes', $attr);
					$checkedItemCount++;
				}

				// add it
				$view->addChild($child);
			}

			// If we created radio buttons, and none of them were checked,
			// then check the first one
			if ((!$multiselect) && ($checkedItemCount==0) && ($firstChild!=null))
			{
				$attr = $firstChild->get('attributes', array());
				$attr['checked'] = 'checked';
				$firstChild->set('attributes', $attr);
			}
		}

		return $view;
	}

}