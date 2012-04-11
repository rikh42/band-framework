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
			$value = $this->get('value');
			$checkedItemCount = 0;
			foreach ($choices as $key=>$title)
			{
				// make a checkbox for each item please.
				$child = new FormView();

				// Copy over any properties into the view
				foreach($this->properties as $name=>$prop)
				{
					$child->set($name, $prop);
				}

				// Custom values that are generated or outside the standard properties
				$child->set('id', $this->getId().'-'.$key);
				$child->set('label', $title);
				$child->set('full_name', $this->getFullName().'['.$key.']');
				$child->set('value', $key);

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
				if ($key == $value)
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
			if ((!$multiselect) && ($checkedItemCount==0))
			{
				$attr = $view[0]->get('attributes', array());
				$attr['checked'] = 'checked';
				$view[0]->set('attributes', $attr);
			}
		}

		return $view;
	}

}