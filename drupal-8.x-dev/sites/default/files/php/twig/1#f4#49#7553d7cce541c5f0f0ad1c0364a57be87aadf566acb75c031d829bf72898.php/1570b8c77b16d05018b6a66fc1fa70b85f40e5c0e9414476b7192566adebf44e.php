<?php

/* core/modules/toolbar/templates/toolbar.html.twig */
class __TwigTemplate_f4497553d7cce541c5f0f0ad1c0364a57be87aadf566acb75c031d829bf72898 extends Drupal\Core\Template\TwigTemplate
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
        // line 25
        echo "<nav";
        echo twig_render_var($this->getContextReference($context, "attributes"));
        echo ">
  <div";
        // line 26
        echo twig_render_var($this->getContextReference($context, "toolbar_attributes"));
        echo ">
    <h2 class=\"visually-hidden\">";
        // line 27
        echo twig_render_var($this->getContextReference($context, "toolbar_heading"));
        echo "</h2>
    ";
        // line 28
        if (isset($context["tabs"])) { $_tabs_ = $context["tabs"]; } else { $_tabs_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_tabs_);
        foreach ($context['_seq'] as $context["_key"] => $context["tab"]) {
            // line 29
            echo "      <div";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "tab"), "attributes"));
            echo ">";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "tab"), "link"));
            echo "</div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tab'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 31
        echo "  </div>
  ";
        // line 32
        if (isset($context["trays"])) { $_trays_ = $context["trays"]; } else { $_trays_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_trays_);
        foreach ($context['_seq'] as $context["_key"] => $context["tray"]) {
            // line 33
            echo "    ";
            ob_start();
            // line 34
            echo "    <div";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "tray"), "attributes"));
            echo ">
      <div class=\"toolbar-lining clearfix\">
        ";
            // line 36
            if (isset($context["tray"])) { $_tray_ = $context["tray"]; } else { $_tray_ = null; }
            if ($this->getAttribute($_tray_, "label")) {
                // line 37
                echo "          <h3 class=\"toolbar-tray-name visually-hidden\">";
                echo twig_render_var($this->getAttribute($this->getContextReference($context, "tray"), "label"));
                echo "</h3>
        ";
            }
            // line 39
            echo "        ";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "tray"), "links"));
            echo "
      </div>
    </div>
    ";
            echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
            // line 43
            echo "  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tray'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 44
        echo "  ";
        echo twig_render_var($this->getContextReference($context, "remainder"));
        echo "
</nav>
";
    }

    public function getTemplateName()
    {
        return "core/modules/toolbar/templates/toolbar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  88 => 44,  74 => 39,  68 => 37,  65 => 36,  59 => 34,  56 => 33,  32 => 28,  28 => 27,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 41,  24 => 26,  55 => 32,  52 => 30,  49 => 29,  46 => 27,  43 => 46,  38 => 24,  35 => 23,  33 => 43,  26 => 20,  23 => 19,  19 => 25,);
    }
}
