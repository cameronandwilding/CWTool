<?php

/**
 * @file
 * Contains Drupal\system\Tests\Common\RenderElementTypesTest.
 */

namespace Drupal\system\Tests\Common;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Url;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Language\Language;
use Drupal\Core\Template\Attribute;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the markup of core render element types passed to drupal_render().
 */
class RenderElementTypesTest extends WebTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Render element types',
      'description' => 'Tests the markup of core render element types passed to drupal_render().',
      'group' => 'Common',
    );
  }

  /**
   * Asserts that an array of elements is rendered properly.
   *
   * @param array $elements
   *   An array of associative arrays describing render elements and their
   *   expected markup. Each item in $elements must contain the following:
   *   - 'name': This human readable description will be displayed on the test
   *     results page.
   *   - 'value': This is the render element to test.
   *   - 'expected': This is the expected markup for the element in 'value'.
   */
  function assertElements($elements) {
    foreach($elements as $element) {
      // More complicated "expected" strings may contain placeholders.
      if (!empty($element['placeholders'])) {
        $element['expected'] = String::format($element['expected'], $element['placeholders']);
      }

      // We don't care about whitespace for the sake of comparing markup.
      $value = new \DOMDocument();
      $value->preserveWhiteSpace = FALSE;
      $value->loadXML(drupal_render($element['value']));

      $expected = new \DOMDocument();
      $expected->preserveWhiteSpace = FALSE;
      $expected->loadXML($element['expected']);

      $message = isset($element['name']) ? '"' . $element['name'] . '" input rendered correctly by drupal_render().' : NULL;
      $this->assertIdentical($value->saveXML(), $expected->saveXML(), $message);
    }
  }

  /**
   * Tests system #type 'container'.
   */
  function testContainer() {
    $elements = array(
      // Basic container with no attributes.
      array(
        'name' => "#type 'container' with no HTML attributes",
        'value' => array(
          '#type' => 'container',
          '#markup' => 'foo',
        ),
        'expected' => '<div>foo</div>',
      ),
      // Container with a class.
      array(
        'name' => "#type 'container' with a class HTML attribute",
        'value' => array(
          '#type' => 'container',
          '#markup' => 'foo',
          '#attributes' => array(
            'class' => 'bar',
          ),
        ),
        'expected' => '<div class="bar">foo</div>',
      ),
      // Container with children.
      array(
        'name' => "#type 'container' with child elements",
        'value' => array(
          '#type' => 'container',
          'child' => array(
            '#markup' => 'foo',
          ),
        ),
        'expected' => '<div>foo</div>',
      ),
    );

    $this->assertElements($elements);
  }

  /**
   * Tests system #type 'html_tag'.
   */
  function testHtmlTag() {
    $elements = array(
      // Test auto-closure meta tag generation.
      array(
        'name' => "#type 'html_tag' auto-closure meta tag generation",
        'value' => array(
          '#type' => 'html_tag',
          '#tag' => 'meta',
          '#attributes' => array(
            'name' => 'description',
            'content' => 'Drupal test',
          ),
        ),
        'expected' => '<meta name="description" content="Drupal test" />' . "\n",
      ),
      // Test title tag generation.
      array(
        'name' => "#type 'html_tag' title tag generation",
        'value' => array(
          '#type' => 'html_tag',
          '#tag' => 'title',
          '#value' => 'title test',
        ),
        'expected' => '<title>title test</title>' . "\n",
      ),
    );

    $this->assertElements($elements);
  }

  /**
   * Tests common #theme 'maintenance_page'.
   */
  function testMaintenancePage() {
    // We need to simulate a lot of what would happen in the preprocess, or
    // there's no way to make these tests portable.

    // HTML element attributes.
    $html_attributes = new Attribute;
    $language_interface = \Drupal::service('language_manager')->getCurrentLanguage();
    $html_attributes['lang'] = $language_interface->id;
    $html_attributes['dir'] = $language_interface->direction ? 'rtl' : 'ltr';

    $site_config = \Drupal::config('system.site');
    $site_name = $site_config->get('name');
    $site_slogan = $site_config->get('slogan');
    if ($title = drupal_get_title()) {
      $head_title = array(
        'title' => strip_tags($title),
        'name' => String::checkPlain($site_config->get('name')),
      );
    }
    else {
      $head_title = array('name' => String::checkPlain($site_name));
      if ($site_slogan) {
        $head_title['slogan'] = strip_tags(Xss::filterAdmin($site_slogan));
      }
    }
    $head_title = implode(' | ', $head_title);

    // Add favicon.
    $favicon = theme_get_setting('favicon.url');
    $type = theme_get_setting('favicon.mimetype');
    drupal_add_html_head_link(array('rel' => 'shortcut icon', 'href' => Url::stripDangerousProtocols($favicon), 'type' => $type));

    // Build CSS links.
    drupal_static_reset('_drupal_add_css');
    $path = drupal_get_path('module', 'system');
    $default_css = array(
      '#attached' => array(
        'css' => array(
          $path . '/css/system.module.css',
          $path . '/css/system.admin.css',
          $path . '/css/system.maintenance.css',
          $path . '/css/system.theme.css',
        ),
      ),
    );
    drupal_render($default_css);
    $css = _drupal_add_css();

    // Simulate the expected output of a "vanilla" maintenance page.
    $expected = <<<EOT
<!DOCTYPE html>
<html!html_attributes>
  <head>
    !head
    <title>!head_title</title>
    !styles
    !scripts
  </head>
  <body class="!attributes.class">
    <div class="l-container">
      <header role="banner">
        <a href="!front_page" title="Home" rel="home">
          <img src="!logo" alt="Home"/>
        </a>
        <div class="name-and-slogan">
          <h1 class="site-name">
            <a href="!front_page" title="Home" rel="home">!site_name</a>
          </h1>
        </div>
      </header>
      <main role="main">
        !title
        !content
      </main>
    </div>
  </body>
</html>
EOT;

    $placeholders = array(
      '!html_attributes' => $html_attributes->__toString(),
      '!head' => drupal_get_html_head(),
      '!head_title' => $head_title,
      '!styles' => drupal_get_css($css),
      '!scripts' => drupal_get_js(),
      '!attributes.class' => 'maintenance-page in-maintenance no-sidebars',
      '!front_page' => url(),
      '!logo' => theme_get_setting('logo.url'),
      '!site_name' => $site_config->get('name'),
      '!title' => $title ? '<h1>' . $title . '</h1>' : '',
      '!content' => '<span>foo</span>',
    );

    // We have to reset drupal_add_css between each test.
    drupal_static_reset('_drupal_add_css');

    // Test basic string for maintenance page content.
    $elements = array(
      array(
        'name' => "#theme 'maintenance_page' with content of <span>foo</span>",
        'value' => array(
          '#theme' => 'maintenance_page',
          '#content' => '<span>foo</span>',
          '#show_messages' => FALSE,
        ),
        'expected' => $expected,
        'placeholders' => $placeholders,
      ),
    );
    $this->assertElements($elements);

    // Test render array for maintenance page content.
    drupal_static_reset('_drupal_add_css');
    $elements[0]['name'] = "#theme 'maintenance_page' with content as a render array";
    $elements[0]['value']['#content'] = array('#markup' => '<span>foo</span>');
    $this->assertElements($elements);
  }

}
