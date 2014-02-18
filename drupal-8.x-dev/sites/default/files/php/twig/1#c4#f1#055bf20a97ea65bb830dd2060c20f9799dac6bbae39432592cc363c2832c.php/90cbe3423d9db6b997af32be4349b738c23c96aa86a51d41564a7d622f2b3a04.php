<?php

/* core/modules/system/templates/breadcrumb.html.twig */
class __TwigTemplate_c4f1055bf20a97ea65bb830dd2060c20f9799dac6bbae39432592cc363c2832c extends Drupal\Core\Template\TwigTemplate
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
        // line 12
        if (isset($context["breadcrumb"])) { $_breadcrumb_ = $context["breadcrumb"]; } else { $_breadcrumb_ = null; }
        if ($_breadcrumb_) {
            // line 13
            echo "  <nav class=\"breadcrumb\" role=\"navigation\">
    <h2 class=\"visually-hidden\">";
            // line 14
            echo twig_render_var(t("You are here"));
            echo "</h2>
    <ol>
    ";
            // line 16
            if (isset($context["breadcrumb"])) { $_breadcrumb_ = $context["breadcrumb"]; } else { $_breadcrumb_ = null; }
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($_breadcrumb_);
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 17
                echo "      <li>";
                echo twig_render_var($this->getContextReference($context, "item"));
                echo "</li>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 19
            echo "    </ol>
  </nav>
";
        }
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/breadcrumb.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  118 => 107,  110 => 103,  104 => 100,  101 => 99,  97 => 98,  91 => 95,  84 => 93,  78 => 91,  44 => 19,  41 => 75,  36 => 74,  30 => 16,  81 => 58,  75 => 90,  72 => 54,  62 => 50,  50 => 78,  25 => 14,  22 => 13,  88 => 94,  74 => 39,  68 => 86,  65 => 85,  59 => 83,  56 => 82,  32 => 28,  28 => 41,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 71,  24 => 26,  55 => 48,  52 => 30,  49 => 29,  46 => 46,  43 => 46,  38 => 43,  35 => 17,  33 => 43,  26 => 20,  23 => 70,  19 => 12,);
    }
}
