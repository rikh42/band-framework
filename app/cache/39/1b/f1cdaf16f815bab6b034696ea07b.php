<?php

/* ::forms.layout.twig */
class __TwigTemplate_391bf1cdaf16f815bab6b034696ea07b extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'field_label' => array($this, 'block_field_label'),
            'field_errors' => array($this, 'block_field_errors'),
            'field_widget' => array($this, 'block_field_widget'),
            'field_row' => array($this, 'block_field_row'),
            'form_row' => array($this, 'block_form_row'),
            'field_rows' => array($this, 'block_field_rows'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "
";
        // line 3
        $this->displayBlock('field_label', $context, $blocks);
        // line 6
        echo "
";
        // line 8
        $this->displayBlock('field_errors', $context, $blocks);
        // line 17
        echo "

";
        // line 20
        $this->displayBlock('field_widget', $context, $blocks);
        // line 28
        echo "

";
        // line 31
        $this->displayBlock('field_row', $context, $blocks);
        // line 38
        echo "

";
        // line 41
        $this->displayBlock('form_row', $context, $blocks);
        // line 52
        echo "

";
        // line 55
        $this->displayBlock('field_rows', $context, $blocks);
        // line 60
        echo "
";
    }

    // line 3
    public function block_field_label($context, array $blocks = array())
    {
        // line 4
        echo "<label class=\"control-label\" for=\"";
        if (isset($context["id"])) { $_id_ = $context["id"]; } else { $_id_ = null; }
        if (isset($context["name"])) { $_name_ = $context["name"]; } else { $_name_ = null; }
        echo twig_escape_filter($this->env, ((array_key_exists("id", $context)) ? (_twig_default_filter($_id_, $_name_)) : ($_name_)), "html", null, true);
        echo "\">";
        if (isset($context["label"])) { $_label_ = $context["label"]; } else { $_label_ = null; }
        if (isset($context["name"])) { $_name_ = $context["name"]; } else { $_name_ = null; }
        echo twig_escape_filter($this->env, ((array_key_exists("label", $context)) ? (_twig_default_filter($_label_, $_name_)) : ($_name_)), "html", null, true);
        echo "</label>
";
    }

    // line 8
    public function block_field_errors($context, array $blocks = array())
    {
        // line 9
        echo "\t";
        if (isset($context["errors"])) { $_errors_ = $context["errors"]; } else { $_errors_ = null; }
        if ($_errors_) {
            // line 10
            echo "\t\t<ul>
\t\t";
            // line 11
            if (isset($context["errors"])) { $_errors_ = $context["errors"]; } else { $_errors_ = null; }
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($_errors_);
            foreach ($context['_seq'] as $context["_key"] => $context["e"]) {
                // line 12
                echo "\t\t\t<li>";
                if (isset($context["e"])) { $_e_ = $context["e"]; } else { $_e_ = null; }
                echo twig_escape_filter($this->env, $_e_, "html", null, true);
                echo "</li>
\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['e'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 14
            echo "\t\t</ul>
\t";
        }
    }

    // line 20
    public function block_field_widget($context, array $blocks = array())
    {
        // line 21
        echo "<div class=\"controls\">
\t<input class=\"input-xlarge\" id=\"";
        // line 22
        if (isset($context["id"])) { $_id_ = $context["id"]; } else { $_id_ = null; }
        echo twig_escape_filter($this->env, $_id_, "html", null, true);
        echo "\" type=\"";
        if (isset($context["type"])) { $_type_ = $context["type"]; } else { $_type_ = null; }
        echo twig_escape_filter($this->env, ((array_key_exists("type", $context)) ? (_twig_default_filter($_type_, "text")) : ("text")), "html", null, true);
        echo "\" name=\"";
        if (isset($context["full_name"])) { $_full_name_ = $context["full_name"]; } else { $_full_name_ = null; }
        echo twig_escape_filter($this->env, $_full_name_, "html", null, true);
        echo "\" value=\"";
        if (isset($context["value"])) { $_value_ = $context["value"]; } else { $_value_ = null; }
        echo twig_escape_filter($this->env, $_value_, "html", null, true);
        echo "\" />
\t";
        // line 23
        if (isset($context["hint"])) { $_hint_ = $context["hint"]; } else { $_hint_ = null; }
        if ($_hint_) {
            // line 24
            echo "\t\t<p class=\"help-block\">";
            if (isset($context["hint"])) { $_hint_ = $context["hint"]; } else { $_hint_ = null; }
            echo twig_escape_filter($this->env, $_hint_, "html", null, true);
            echo "</p>
\t";
        }
        // line 26
        echo "</div>
";
    }

    // line 31
    public function block_field_row($context, array $blocks = array())
    {
        // line 32
        echo "<div class=\"control-group\">
\t";
        // line 33
        if (isset($context["form"])) { $_form_ = $context["form"]; } else { $_form_ = null; }
        echo $this->env->getExtension('forms')->renderFieldLabel($_form_);
        echo "
\t";
        // line 34
        if (isset($context["form"])) { $_form_ = $context["form"]; } else { $_form_ = null; }
        echo $this->env->getExtension('forms')->renderFieldErrors($_form_);
        echo "
\t";
        // line 35
        if (isset($context["form"])) { $_form_ = $context["form"]; } else { $_form_ = null; }
        echo $this->env->getExtension('forms')->renderFieldWidgets($_form_);
        echo "
</div>
";
    }

    // line 41
    public function block_form_row($context, array $blocks = array())
    {
        // line 42
        echo "\t";
        if (isset($context["label"])) { $_label_ = $context["label"]; } else { $_label_ = null; }
        if ($_label_) {
            // line 43
            echo "\t<fieldset><legend>";
            if (isset($context["label"])) { $_label_ = $context["label"]; } else { $_label_ = null; }
            echo twig_escape_filter($this->env, $_label_, "html", null, true);
            echo "</legend>
\t";
        }
        // line 45
        echo "\t";
        if (isset($context["form"])) { $_form_ = $context["form"]; } else { $_form_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_form_);
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 46
            echo "\t\t";
            if (isset($context["child"])) { $_child_ = $context["child"]; } else { $_child_ = null; }
            echo $this->env->getExtension('forms')->renderFormRow($_child_);
            echo "
\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 48
        echo "\t";
        if (isset($context["label"])) { $_label_ = $context["label"]; } else { $_label_ = null; }
        if ($_label_) {
            // line 49
            echo "\t</fieldset>
\t";
        }
    }

    // line 55
    public function block_field_rows($context, array $blocks = array())
    {
        // line 56
        echo "\t";
        if (isset($context["form"])) { $_form_ = $context["form"]; } else { $_form_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_form_);
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 57
            echo "\t\t";
            if (isset($context["child"])) { $_child_ = $context["child"]; } else { $_child_ = null; }
            echo $this->env->getExtension('forms')->renderFormRow($_child_);
            echo "
\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
    }

    public function getTemplateName()
    {
        return "::forms.layout.twig";
    }

}
