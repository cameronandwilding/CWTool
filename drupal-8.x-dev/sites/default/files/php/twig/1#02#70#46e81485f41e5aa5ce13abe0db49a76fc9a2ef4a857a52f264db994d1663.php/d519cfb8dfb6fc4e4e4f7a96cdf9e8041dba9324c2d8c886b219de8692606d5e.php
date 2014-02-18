<?php

/* core/modules/system/templates/status-messages.html.twig */
class __TwigTemplate_027046e81485f41e5aa5ce13abe0db49a76fc9a2ef4a857a52f264db994d1663 extends Drupal\Core\Template\TwigTemplate
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
        // line 26
        if (isset($context["message_list"])) { $_message_list_ = $context["message_list"]; } else { $_message_list_ = null; }
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($_message_list_);
        foreach ($context['_seq'] as $context["type"] => $context["messages"]) {
            // line 27
            echo "  <div class=\"messages messages--";
            echo twig_render_var($this->getContextReference($context, "type"));
            echo "\" role=\"contentinfo\" aria-label=\"";
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "status_headings"), $this->getContextReference($context, "type"), array(), "array"));
            echo "\">
    ";
            // line 28
            if (isset($context["type"])) { $_type_ = $context["type"]; } else { $_type_ = null; }
            if (($_type_ == "error")) {
                // line 29
                echo "      <div role=\"alert\">
    ";
            }
            // line 31
            echo "      ";
            if (isset($context["status_headings"])) { $_status_headings_ = $context["status_headings"]; } else { $_status_headings_ = null; }
            if (isset($context["type"])) { $_type_ = $context["type"]; } else { $_type_ = null; }
            if ($this->getAttribute($_status_headings_, $_type_, array(), "array")) {
                // line 32
                echo "        <h2 class=\"visually-hidden\">";
                echo twig_render_var($this->getAttribute($this->getContextReference($context, "status_headings"), $this->getContextReference($context, "type"), array(), "array"));
                echo "</h2>
      ";
            }
            // line 34
            echo "      ";
            if (isset($context["messages"])) { $_messages_ = $context["messages"]; } else { $_messages_ = null; }
            if ((twig_length_filter($this->env, $_messages_) > 1)) {
                // line 35
                echo "        <ul class=\"messages__list\">
          ";
                // line 36
                if (isset($context["messages"])) { $_messages_ = $context["messages"]; } else { $_messages_ = null; }
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($_messages_);
                foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                    // line 37
                    echo "            <li class=\"messages__item\">";
                    echo twig_render_var($this->getContextReference($context, "message"));
                    echo "</li>
          ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 39
                echo "        </ul>
      ";
            } else {
                // line 41
                echo "        ";
                echo twig_render_var($this->getAttribute($this->getContextReference($context, "messages"), 0));
                echo "
      ";
            }
            // line 43
            echo "    ";
            if (isset($context["type"])) { $_type_ = $context["type"]; } else { $_type_ = null; }
            if (($_type_ == "error")) {
                // line 44
                echo "      </div>
    ";
            }
            // line 46
            echo "  </div>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['messages'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/status-messages.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  80 => 43,  70 => 39,  53 => 35,  34 => 29,  31 => 28,  118 => 107,  110 => 103,  104 => 100,  101 => 99,  97 => 98,  91 => 95,  84 => 44,  78 => 91,  44 => 19,  41 => 75,  36 => 74,  30 => 16,  81 => 58,  75 => 90,  72 => 54,  62 => 50,  50 => 78,  25 => 14,  22 => 13,  88 => 46,  74 => 41,  68 => 86,  65 => 85,  59 => 83,  56 => 36,  32 => 28,  28 => 41,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 37,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 71,  24 => 27,  55 => 48,  52 => 30,  49 => 34,  46 => 46,  43 => 32,  38 => 31,  35 => 17,  33 => 43,  26 => 20,  23 => 70,  19 => 26,);
    }
}
