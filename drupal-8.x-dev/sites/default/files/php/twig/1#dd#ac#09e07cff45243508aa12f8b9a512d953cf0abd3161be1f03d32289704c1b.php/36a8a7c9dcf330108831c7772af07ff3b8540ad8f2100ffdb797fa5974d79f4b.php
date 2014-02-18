<?php

/* core/modules/block/templates/block.html.twig */
class __TwigTemplate_ddac09e07cff45243508aa12f8b9a512d953cf0abd3161be1f03d32289704c1b extends Drupal\Core\Template\TwigTemplate
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
        // line 44
        echo "<div";
        echo twig_render_var($this->getContextReference($context, "attributes"));
        echo ">
  ";
        // line 45
        echo twig_render_var($this->getContextReference($context, "title_prefix"));
        echo "
  ";
        // line 46
        if (isset($context["label"])) { $_label_ = $context["label"]; } else { $_label_ = null; }
        if ($_label_) {
            // line 47
            echo "    <h2";
            echo twig_render_var($this->getContextReference($context, "title_attributes"));
            echo ">";
            echo twig_render_var($this->getContextReference($context, "label"));
            echo "</h2>
  ";
        }
        // line 49
        echo "  ";
        echo twig_render_var($this->getContextReference($context, "title_suffix"));
        echo "

  <div";
        // line 51
        echo twig_render_var($this->getContextReference($context, "content_attributes"));
        echo ">
    ";
        // line 52
        echo twig_render_var($this->getContextReference($context, "content"));
        echo "
  </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "core/modules/block/templates/block.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  45 => 51,  39 => 49,  80 => 43,  70 => 39,  53 => 35,  34 => 29,  31 => 47,  118 => 107,  110 => 103,  104 => 100,  101 => 99,  97 => 98,  91 => 95,  84 => 44,  78 => 91,  44 => 19,  41 => 75,  36 => 74,  30 => 16,  81 => 58,  75 => 90,  72 => 54,  62 => 50,  50 => 78,  25 => 14,  22 => 13,  88 => 46,  74 => 41,  68 => 86,  65 => 85,  59 => 83,  56 => 36,  32 => 28,  28 => 46,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 37,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 71,  24 => 45,  55 => 48,  52 => 30,  49 => 52,  46 => 46,  43 => 32,  38 => 31,  35 => 17,  33 => 43,  26 => 20,  23 => 70,  19 => 44,);
    }
}
