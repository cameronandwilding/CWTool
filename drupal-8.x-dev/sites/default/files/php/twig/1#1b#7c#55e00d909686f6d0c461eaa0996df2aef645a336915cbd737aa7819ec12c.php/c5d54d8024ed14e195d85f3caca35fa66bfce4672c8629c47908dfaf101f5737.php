<?php

/* core/themes/seven/templates/page.html.twig */
class __TwigTemplate_1b7c55e00d909686f6d0c461eaa0996df2aef645a336915cbd737aa7819ec12c extends Drupal\Core\Template\TwigTemplate
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
        // line 68
        echo "  <header id=\"branding\" class=\"clearfix\">
    <div class=\"branding__inner\">
      ";
        // line 70
        echo twig_render_var($this->getContextReference($context, "title_prefix"));
        echo "
      ";
        // line 71
        if (isset($context["title"])) { $_title_ = $context["title"]; } else { $_title_ = null; }
        if ($_title_) {
            // line 72
            echo "        <h1 class=\"page-title\">";
            echo twig_render_var($this->getContextReference($context, "title"));
            echo "</h1>
      ";
        }
        // line 74
        echo "      ";
        echo twig_render_var($this->getContextReference($context, "title_suffix"));
        echo "
      ";
        // line 75
        if (isset($context["primary_local_tasks"])) { $_primary_local_tasks_ = $context["primary_local_tasks"]; } else { $_primary_local_tasks_ = null; }
        if ($_primary_local_tasks_) {
            // line 76
            echo "        ";
            echo twig_render_var($this->getContextReference($context, "primary_local_tasks"));
            echo "
      ";
        }
        // line 78
        echo "    </div>
  </header>

  <div id=\"page\">
    ";
        // line 82
        if (isset($context["secondary_local_tasks"])) { $_secondary_local_tasks_ = $context["secondary_local_tasks"]; } else { $_secondary_local_tasks_ = null; }
        if ($_secondary_local_tasks_) {
            // line 83
            echo "      <div class=\"tabs-secondary clearfix\" role=\"navigation\">";
            echo twig_render_var($this->getContextReference($context, "secondary_local_tasks"));
            echo "</div>
    ";
        }
        // line 85
        echo "
    ";
        // line 86
        echo twig_render_var($this->getContextReference($context, "breadcrumb"));
        echo "

    <main id=\"content\" class=\"clearfix\" role=\"main\">
      <div class=\"visually-hidden\"><a id=\"main-content\"></a></div>
      ";
        // line 90
        if (isset($context["messages"])) { $_messages_ = $context["messages"]; } else { $_messages_ = null; }
        if ($_messages_) {
            // line 91
            echo "        <div id=\"console\" class=\"clearfix\">";
            echo twig_render_var($this->getContextReference($context, "messages"));
            echo "</div>
      ";
        }
        // line 93
        echo "      ";
        if (isset($context["page"])) { $_page_ = $context["page"]; } else { $_page_ = null; }
        if ($this->getAttribute($_page_, "help")) {
            // line 94
            echo "        <div id=\"help\">
          ";
            // line 95
            echo twig_render_var($this->getAttribute($this->getContextReference($context, "page"), "help"));
            echo "
        </div>
      ";
        }
        // line 98
        echo "      ";
        if (isset($context["action_links"])) { $_action_links_ = $context["action_links"]; } else { $_action_links_ = null; }
        if ($_action_links_) {
            // line 99
            echo "        <ul class=\"action-links\">
          ";
            // line 100
            echo twig_render_var($this->getContextReference($context, "action_links"));
            echo "
        </ul>
      ";
        }
        // line 103
        echo "      ";
        echo twig_render_var($this->getAttribute($this->getContextReference($context, "page"), "content"));
        echo "
    </main>

    <footer id=\"footer\" role=\"contentinfo\">
      ";
        // line 107
        echo twig_render_var($this->getContextReference($context, "feed_icons"));
        echo "
    </footer>

  </div>
";
    }

    public function getTemplateName()
    {
        return "core/themes/seven/templates/page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  118 => 107,  110 => 103,  104 => 100,  101 => 99,  97 => 98,  91 => 95,  84 => 93,  78 => 91,  44 => 76,  41 => 75,  36 => 74,  30 => 72,  81 => 58,  75 => 90,  72 => 54,  62 => 50,  50 => 78,  25 => 40,  22 => 24,  88 => 94,  74 => 39,  68 => 86,  65 => 85,  59 => 83,  56 => 82,  32 => 28,  28 => 41,  82 => 43,  76 => 55,  71 => 54,  67 => 53,  61 => 51,  57 => 50,  51 => 32,  48 => 31,  37 => 29,  27 => 71,  24 => 26,  55 => 48,  52 => 30,  49 => 29,  46 => 46,  43 => 46,  38 => 43,  35 => 23,  33 => 43,  26 => 20,  23 => 70,  19 => 68,);
    }
}
