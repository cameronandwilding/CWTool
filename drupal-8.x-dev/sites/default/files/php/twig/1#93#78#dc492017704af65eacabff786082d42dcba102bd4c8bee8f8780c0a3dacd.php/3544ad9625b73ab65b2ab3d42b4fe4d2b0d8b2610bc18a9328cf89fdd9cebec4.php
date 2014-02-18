<?php

/* core/modules/system/templates/links.html.twig */
class __TwigTemplate_9378dc492017704af65eacabff786082d42dcba102bd4c8bee8f8780c0a3dacd extends Drupal\Core\Template\TwigTemplate
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
        // line 38
        if (isset($context["links"])) { $_links_ = $context["links"]; } else { $_links_ = null; }
        if ($_links_) {
            // line 39
            if (isset($context["heading"])) { $_heading_ = $context["heading"]; } else { $_heading_ = null; }
            if ($_heading_) {
                // line 40
                if (isset($context["heading"])) { $_heading_ = $context["heading"]; } else { $_heading_ = null; }
                if ($this->getAttribute($_heading_, "level")) {
                    // line 41
                    echo "<";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "level"));
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "attributes"));
                    echo ">";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "text"));
                    echo "</";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "level"));
                    echo ">";
                } else {
                    // line 43
                    echo "<h2";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "attributes"));
                    echo ">";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "heading"), "text"));
                    echo "</h2>";
                }
            }
            // line 46
            echo "<ul";
            echo twig_render_var($this->getContextReference($context, "attributes"));
            echo ">";
            // line 47
            if (isset($context["links"])) { $_links_ = $context["links"]; } else { $_links_ = null; }
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($_links_);
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 48
                echo "<li";
                echo twig_render_var($this->getAttribute($this->getContextReference($context, "item"), "attributes"));
                echo ">";
                // line 49
                if (isset($context["item"])) { $_item_ = $context["item"]; } else { $_item_ = null; }
                if ($this->getAttribute($_item_, "link")) {
                    // line 50
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "item"), "link"));
                } elseif ($this->getAttribute($_item_, "text_attributes")) {
                    // line 52
                    echo "<span";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "item"), "text_attributes"));
                    echo ">";
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "item"), "text"));
                    echo "</span>";
                } else {
                    // line 54
                    echo twig_render_var($this->getAttribute($this->getContextReference($context, "item"), "text"));
                }
                // line 56
                echo "</li>";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 58
            echo "</ul>";
        }
    }

    public function getTemplateName()
    {
        return "core/modules/system/templates/links.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  81 => 58,  75 => 56,  72 => 54,  62 => 50,  50 => 47,  25 => 40,  22 => 39,  88 => 44,  74 => 39,  68 => 37,  65 => 52,  59 => 49,  56 => 33,  32 => 28,  28 => 41,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 41,  24 => 26,  55 => 48,  52 => 30,  49 => 29,  46 => 46,  43 => 46,  38 => 43,  35 => 23,  33 => 43,  26 => 20,  23 => 19,  19 => 38,);
    }
}
