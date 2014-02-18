<?php

/* core/modules/system/templates/region.html.twig */
class __TwigTemplate_b52475bd5211549c54a8578a9439adf6153af46b721553feb2875aced3b8eccc extends Drupal\Core\Template\TwigTemplate
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
        // line 23
        if (isset($context["content"])) { $_content_ = $context["content"]; } else { $_content_ = null; }
        if ($_content_) {
            // line 24
            echo "  <div";
            echo twig_render_var($this->getContextReference($context, "attributes"));
            echo ">
    ";
            // line 25
            echo twig_render_var($this->getContextReference($context, "content"));
            echo "
  </div>
";
        }
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/region.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  81 => 58,  75 => 56,  72 => 54,  62 => 50,  50 => 47,  25 => 40,  22 => 24,  88 => 44,  74 => 39,  68 => 37,  65 => 52,  59 => 49,  56 => 33,  32 => 28,  28 => 41,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 25,  24 => 26,  55 => 48,  52 => 30,  49 => 29,  46 => 46,  43 => 46,  38 => 43,  35 => 23,  33 => 43,  26 => 20,  23 => 19,  19 => 23,);
    }
}
