<?php

/* example::layout.twig */
class __TwigTemplate_fd1f2284a0c8de75a65c5fe565f916b3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'header' => array($this, 'block_header'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
\t<meta charset=\"utf-8\">
\t<title>Example</title>
\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">

\t<link href=\"/css/bootstrap.min.css\" rel=\"stylesheet\">
\t<link href=\"/css/bootstrap-responsive.min.css\" rel=\"stylesheet\">
\t<link href=\"/css/doc.css\" rel=\"stylesheet\">
</head>
<body onload=\"prettyPrint()\">


<header class=\"hero-unit\">
\t<h1>Band Framework</h1>
\t<p>A lightweight Symfony-like framework for PHP apps.</p>
</header>

<section class=\"main-content\">
\t<div class=\"container\">
\t\t<div class=\"row\">
\t\t\t<div class=\"span12\">
\t\t\t\t";
        // line 24
        $this->displayBlock('header', $context, $blocks);
        // line 30
        echo "
\t\t\t\t<div class=\"row\">
\t\t\t\t\t<div class=\"span8\">
\t\t\t\t\t\t";
        // line 33
        $this->displayBlock('content', $context, $blocks);
        // line 36
        echo "\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"span4\">
\t\t\t\t\t\t<h2>Credits</h2>

\t\t\t\t\t\t<p>The core framework is based loosely on an older framework I developed. This
\t\t\t\t\t\t\tnewer version has been heavily influenced by the design of the Symfony 2 framework.
\t\t\t\t\t\t\tIn fact, some parts of Symfony 2 are used directly (especially the Yaml parser
\t\t\t\t\t\t\tand the Twig template engine).</p>

\t\t\t\t\t\t<p>Many parts of the core framework, such as the dependency injection system,
\t\t\t\t\t\t\tthe response and request objects are kind of similar to Symfony 2. The versions
\t\t\t\t\t\t\tin this framework tend to be simpler and cut down, with less powerful functionality.
\t\t\t\t\t\t\tThe simplification has been made in the quest for performance and easy of learning.</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t</div>
</section>

<section class=\"footer\">
\t<div class=\"container\">
\t\t<div class=\"row\">
\t\t\t<div class=\"span12\">
\t\t\t\t";
        // line 60
        $this->displayBlock('footer', $context, $blocks);
        // line 64
        echo "\t\t\t</div>
\t\t</div>
\t</div>
</section>

<script src=\"/js/prettify.js\"></script>
</body>
</html>";
    }

    // line 24
    public function block_header($context, array $blocks = array())
    {
        // line 25
        echo "\t\t\t\t<div class=\"page-header\">
\t\t\t\t\t<h1>Page Header</h1>
\t\t\t\t\t<p>and a sample caption</p>
\t\t\t\t</div>
\t\t\t\t";
    }

    // line 33
    public function block_content($context, array $blocks = array())
    {
        // line 34
        echo "\t\t\t\t\t\t<p>This is replaced with the page content.</p>
\t\t\t\t\t\t";
    }

    // line 60
    public function block_footer($context, array $blocks = array())
    {
        // line 61
        echo "\t\t\t\t<p>Designed and built by Rik Heywood, with massive inspiration from Symfony 2</p>
\t\t\t\t<p>Code licensed under the new BSD License. See included license.txt file for details.</p>
\t\t\t\t";
    }

    public function getTemplateName()
    {
        return "example::layout.twig";
    }

}
