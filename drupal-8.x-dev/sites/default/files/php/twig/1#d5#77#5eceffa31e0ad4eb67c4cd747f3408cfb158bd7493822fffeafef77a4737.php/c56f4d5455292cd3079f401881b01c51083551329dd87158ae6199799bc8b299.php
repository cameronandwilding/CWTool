<?php

/* core/modules/system/templates/form-element.html.twig */
class __TwigTemplate_d5775eceffa31e0ad4eb67c4cd747f3408cfb158bd7493822fffeafef77a4737 extends Drupal\Core\Template\TwigTemplate
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
        // line 39
        echo "<div";
        echo twig_render_var($this->getContextReference($context, "attributes"));
        echo ">
  ";
        // line 40
        if (isset($context["label_display"])) { $_label_display_ = $context["label_display"]; } else { $_label_display_ = null; }
        if (twig_in_filter($_label_display_, array(0 => "before", 1 => "invisible"))) {
            // line 41
            echo "    ";
            echo twig_render_var($this->getContextReference($context, "label"));
            echo "
  ";
        }
        // line 43
        echo "  ";
        if (isset($context["prefix"])) { $_prefix_ = $context["prefix"]; } else { $_prefix_ = null; }
        if ((!twig_test_empty($_prefix_))) {
            // line 44
            echo "    <span class=\"field-prefix\">";
            echo twig_render_var($this->getContextReference($context, "prefix"));
            echo "</span>
  ";
        }
        // line 46
        echo "  ";
        echo twig_render_var($this->getContextReference($context, "children"));
        echo "
  ";
        // line 47
        if (isset($context["suffix"])) { $_suffix_ = $context["suffix"]; } else { $_suffix_ = null; }
        if ((!twig_test_empty($_suffix_))) {
            // line 48
            echo "    <span class=\"field-suffix\">";
            echo twig_render_var($this->getContextReference($context, "suffix"));
            echo "</span>
  ";
        }
        // line 50
        echo "  ";
        if (isset($context["label_display"])) { $_label_display_ = $context["label_display"]; } else { $_label_display_ = null; }
        if (($_label_display_ == "after")) {
            // line 51
            echo "    ";
            echo twig_render_var($this->getContextReference($context, "label"));
            echo "
  ";
        }
        // line 53
        echo "  ";
        if (isset($context["description"])) { $_description_ = $context["description"]; } else { $_description_ = null; }
        if ($this->getAttribute($_description_, "content")) {
            // line 54
            echo "    <div";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "description"), "attributes"));
            echo ">
      ";
            // line 55
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "description"), "content"));
            echo "
    </div>
  ";
        }
        // line 58
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/form-element.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  82 => 58,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 48,  48 => 47,  37 => 44,  27 => 41,  24 => 40,  55 => 32,  52 => 30,  49 => 29,  46 => 27,  43 => 46,  38 => 24,  35 => 23,  33 => 43,  26 => 20,  23 => 19,  19 => 39,);
    }
}
