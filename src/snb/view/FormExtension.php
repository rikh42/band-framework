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

namespace snb\view;

use snb\form\FormView;
use snb\core\ConfigInterface;

/**
 * A Twig extension that adds functions to support forms
 */
class FormExtension extends \Twig_Extension
{
    protected $template;
    protected $config;

    /**
     * @param \snb\core\ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->template = null;
        $this->config = $config;
    }

    /**
     * Loads thte forms layout template
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        // Find the forms layout template
        $templateName = $this->config->get('snb.forms.layout', '::forms.layout.twig');
        $this->template = $environment->loadTemplate($templateName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'forms';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'draw_form'  => new \Twig_Function_Method($this, 'renderFormRow', array('is_safe' => array('html'))),
            'form_row'  => new \Twig_Function_Method($this, 'renderFormRow', array('is_safe' => array('html'))),
            'form_label'  => new \Twig_Function_Method($this, 'renderFieldLabel', array('is_safe' => array('html'))),
            'form_widget'  => new \Twig_Function_Method($this, 'renderFieldWidgets', array('is_safe' => array('html'))),
            'form_errors'  => new \Twig_Function_Method($this, 'renderFieldErrors', array('is_safe' => array('html'))),
            'isChoiceSelected'  => new \Twig_Function_Method($this, 'isChoiceSelected', array('is_safe' => array('html')))
        );
    }

    /**
     * Determines if a given choice should be selected for not.
     * This is complicated, as twigs "in" method will treat numbers as strings, and look
     * for substrings. This deals multi-select etc where value might be an array of values etc.
     * @param $option
     * @param $value
     * @return bool
     */
    public function isChoiceSelected($option, $value)
    {
        // If they are the same, we are done
        if ($option == $value)

            return true;

        // if value is an array of selected options, we need to see if the option is in the array
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($option == $item) {
                    return true;
                }
            }
        }

        // Failed to find a valid choice
        return false;
    }

    /**
     * Calls one of the blocks in the template. It tries using a block
     * called <type>_<action>, and if that does not exist, falls back to field_<action>
     * @param  string             $action
     * @param  \snb\form\FormView $data
     * @return string
     */
    public function render($action, $data)
    {
        // make sure we got something
        if ($data == null) {
            return '';
        }

        // In fact, it must be a FormView...
        if (!($data instanceof FormView)) {
            return '';
        }

        // Get to work, finding a suitable block to render the Form view with
        $blocks = $this->template->getBlocks();
        $type = $data->get('type', 'field');

        $typeAction = $type.'_'.$action;
        if (!array_key_exists($typeAction, $blocks)) {
            $typeAction = 'field_'.$action;
        }

        ob_start();
        $this->template->displayBlock($typeAction, $data->all(), $blocks);
        $html = ob_get_clean();

        return $html;
    }

    /**
     * @param  \snb\form\FormView|null $form
     * @return string
     */
    public function renderFormRow($form)
    {
        return $this->render('row', $form);
    }

    /**
     * @param  \snb\form\FormView|null $field
     * @return string
     */
    public function renderFieldLabel($field)
    {
        return $this->render('label', $field);
    }

    /**
     * @param  \snb\form\FormView|null $field
     * @return string
     */
    public function renderFieldWidgets($field)
    {
        return $this->render('widget', $field);
    }

    /**
     * @param  \snb\form\FormView|null $form
     * @return string
     */
    public function renderFieldErrors($form)
    {
        return $this->render('errors', $form);
    }
}
