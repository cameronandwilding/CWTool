<?php

/* core/modules/system/templates/details.html.twig */
class __TwigTemplate_02b3fda45748679970b60aca1369ac4e2e868f1af7027b427030eb2d8c0fdc69 extends Drupal\Core\Template\TwigTemplate
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
        // line 18
        echo "<details";
        echo twig_render_var($this->getContextReference($context, "attributes"));
        echo ">";
        // line 19
        if (isset($context["title"])) { $_title_ = $context["title"]; } else { $_title_ = null; }
        if ($_title_) {
            // line 20
            echo "<summary";
            echo twig_render_var($this->getContextReference($context, "summary_attributes"));
            echo ">";
            echo twig_render_var($this->getContextReference($context, "title"));
            echo "</summary>";
        }
        // line 22
        echo "<div class=\"details-wrapper\">";
        // line 23
        if (isset($context["description"])) { $_description_ = $context["description"]; } else { $_description_ = null; }
        if ($_description_) {
            // line 24
            echo "<div class=\"details-description\">";
            echo twig_render_var($this->getContextReference($context, "description"));
            echo "</div>";
        }
        // line 26
        if (isset($context["children"])) { $_children_ = $context["children"]; } else { $_children_ = null; }
        if ($_children_) {
            // line 27
            echo twig_render_var($this->getContextReference($context, "children"));
        }
        // line 29
        if (isset($context["value"])) { $_value_ = $context["value"]; } else { $_value_ = null; }
        if ($_value_) {
            // line 30
            echo twig_render_var($this->getContextReference($context, "value"));
        }
        // line 32
        echo "</div>
</details>
";
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/details.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 32,  52 => 30,  49 => 29,  46 => 27,  43 => 26,  38 => 24,  35 => 23,  33 => 22,  26 => 20,  23 => 19,  19 => 18,);
    }
}
