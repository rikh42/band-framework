<?php

/* example:DemoController:index.twig */
class __TwigTemplate_e63afb39eb5e12a4f31890dd0c4423f2 extends Twig_Template
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
\t<h1>Demo</h1>

\t<p>This is a simple demo using the Band Framework.</p>
</div>
";
    }

    // line 11
    public function block_content($context, array $blocks = array())
    {
        // line 12
        echo "<h2>Getting Started with the Framework</h2>
<p>If you can read this, then something must be working! yay!</p>
<p>This page has been brought to you by the the <em>DemoController</em>
and the Twig templates found in the <em>example</em> folder.</p>
<p>The code to render this page looks like this...</p>
<pre class=\"prettyprint\">
public function indexAction()
{
\treturn \$this->renderResponse('example:DemoController:index.twig');
}
</pre>
<h2>Other Actions included in the Demo Controller</h2>
<ul>
\t<li>
\t\t<a href=\"";
        // line 26
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("hello"), "html", null, true);
        echo "\">Hello Action</a> - A simple Hello World Action that looks for
\t\ta name in the URL and says Hello to you. For example, to say hello to Rik,
\t\tyou just need to use the URL <a href=\"";
        // line 28
        echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getUrl("hello", array("name" => "Rik")), "html", null, true);
        echo "\">/hello/Rik</a>
\t</li>
</ul>
";
    }

    public function getTemplateName()
    {
        return "example:DemoController:index.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
