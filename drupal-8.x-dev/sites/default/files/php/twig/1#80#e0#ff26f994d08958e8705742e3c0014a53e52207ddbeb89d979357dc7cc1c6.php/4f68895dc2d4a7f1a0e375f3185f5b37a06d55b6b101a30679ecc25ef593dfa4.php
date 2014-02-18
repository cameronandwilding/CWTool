<?php

/* core/modules/system/templates/html.html.twig */
class __TwigTemplate_80e0ff26f994d08958e8705742e3c0014a53e52207ddbeb89d979357dc7cc1c6 extends Drupal\Core\Template\TwigTemplate
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 29
        echo "<!DOCTYPE html>
<html";
        // line 30
        echo twig_render_var($this->getContextReference($context, "html_attributes"));
        echo ">
  <head>
    ";
        // line 32
        echo twig_render_var($this->getContextReference($context, "head"));
        echo "
    <title>";
        // line 33
        echo twig_render_var($this->getContextReference($context, "head_title"));
        echo "</title>
    ";
        // line 34
        echo twig_render_var($this->getContextReference($context, "styles"));
        echo "
    ";
        // line 35
        echo twig_render_var($this->getContextReference($context, "scripts"));
        echo "
  </head>
  <body";
        // line 37
        echo twig_render_var($this->getContextReference($context, "attributes"));
        echo ">
    <a href=\"#main-content\" class=\"visually-hidden focusable skip-link\">
      ";
        // line 39
        echo twig_render_var(t("Skip to main content"));
        echo "
    </a>
    ";
        // line 41
        echo twig_render_var($this->getContextReference($context, "page_top"));
        echo "
    ";
        // line 42
        echo twig_render_var($this->getContextReference($context, "page"));
        echo "
    ";
        // line 43
        echo twig_render_var($this->getContextReference($context, "page_bottom"));
        echo "
  </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/html.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 42,  54 => 41,  45 => 51,  39 => 35,  80 => 43,  70 => 39,  53 => 35,  34 => 29,  31 => 33,  118 => 107,  110 => 103,  104 => 100,  101 => 99,  97 => 98,  91 => 95,  84 => 44,  78 => 91,  44 => 37,  41 => 75,  36 => 74,  30 => 16,  81 => 58,  75 => 90,  72 => 54,  62 => 43,  50 => 78,  25 => 14,  22 => 30,  88 => 46,  74 => 41,  68 => 86,  65 => 85,  59 => 83,  56 => 36,  32 => 28,  28 => 46,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 37,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 32,  24 => 45,  55 => 48,  52 => 30,  49 => 39,  46 => 46,  43 => 32,  38 => 31,  35 => 34,  33 => 43,  26 => 20,  23 => 70,  19 => 29,);
    }
}
