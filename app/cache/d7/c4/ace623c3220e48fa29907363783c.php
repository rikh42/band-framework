<?php

/* example:DemoController:hello.twig */
class __TwigTemplate_d7c4ace623c3220e48fa29907363783c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'header' => array($this, 'block_header'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "example::layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_header($context, array $blocks = array())
    {
        // line 4
        echo "<div class=\"page-header\">
\t<h1>Hello ";
        // line 5
        if (isset($context["name"])) { $_name_ = $context["name"]; } else { $_name_ = null; }
        echo twig_escape_filter($this->env, $_name_, "html", null, true);
        echo "</h1>

\t<p>Says hello to you...</p>
</div>
";
    }

    // line 11
    public function block_content($context, array $blocks = array())
    {
        // line 12
        echo "<h2>How this page works</h2>

<p>The framework matches the URL to the HelloAction of the DemoController using
the following information in the routing table...</p>
<pre class=\"prettyprint\">
hello:
  url: /hello/::{name}
  options: { controller: example:DemoController:hello }
  defaults: { name: 'World' }
</pre>

<p>Once the route has been found, the framework calls the action below...</p>

<pre class=\"prettyprint\">
public function helloAction(\$name)
{
\t// Prepare some data that will be rendered in the template
\t\$data = array(
\t\t'name' => \$name
\t);

\t// render it
\treturn \$this->renderResponse('example:DemoController:hello.twig', \$data);
}
</pre>

<p>As you can see, the name has already been extracted from the URL and is
passed into the action as an argument. We then simply render a Twig template
that inserts the name into the page. We can insert it again here if we like<br>
The name in the URL is: <strong>";
        // line 41
        if (isset($context["name"])) { $_name_ = $context["name"]; } else { $_name_ = null; }
        echo twig_escape_filter($this->env, $_name_, "html", null, true);
        echo "</strong></p>

<p><a href=\"";
        // line 43
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("home"), "html", null, true);
        echo "\">Home</a></p>
";
    }

    public function getTemplateName()
    {
        return "example:DemoController:hello.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
