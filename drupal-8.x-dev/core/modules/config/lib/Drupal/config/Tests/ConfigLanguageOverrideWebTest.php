<?php

/**
 * @file
 * Contains \Drupal\config\Tests\ConfigLanguageOverrideWebTest.
 */

namespace Drupal\config\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests language overrides in configuration through the request.
 */
class ConfigLanguageOverrideWebTest extends WebTestBase {

  public static $modules = array('language', 'system');

  public static function getInfo() {
    return array(
      'name' => 'Language overrides through the request',
      'description' => 'Tests language overrides applied through the website.',
      'group' => 'Configuration',
    );
  }

  function setUp() {
    parent::setUp();
  }

  /**
   * Tests translating the site name.
   */
  function testSiteNameTranslation() {
    $adminUser = $this->drupalCreateUser(array('administer site configuration', 'administer languages'));
    $this->drupalLogin($adminUser);

    // Add a custom lanugage.
    $langcode = 'xx';
    $name = $this->randomName(16);
    $edit = array(
      'predefined_langcode' => 'custom',
      'langcode' => $langcode,
      'name' => $name,
      'direction' => '0',
    );
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add custom language'));

    // Save an override for the XX language.
    $config_name = \Drupal::configFactory()->getLanguageConfigName('xx', 'system.site');
    \Drupal::config($config_name)->set('name', 'XX site name')->save();

    $this->drupalLogout();

    // The home page in English should not have the override.
    $this->drupalGet('');
    $this->assertNoText('XX site name');

    // During path resolution the system.site configuration object is used to
    // determine the front page. This occurs before language negotiation causing
    // the configuration factory to cache an object without the correct
    // overrides. We are testing that the configuration factory is
    // re-initialised after language negotiation. Ensure that it applies when
    // we access the XX front page.
    // @see \Drupal\Core\PathProcessor::processInbound()
    $this->drupalGet('xx');
    $this->assertText('XX site name');

    // Set the xx language to be the default language and delete the English
    // language so the site is no longer multilingual and confirm configuration
    // overrides still work.
    $language_manager = \Drupal::languageManager()->reset();
    $this->assertTrue($language_manager->isMultilingual(), 'The test site is multilingual.');
    $language = \Drupal::languageManager()->getLanguage('xx');
    $language->default = TRUE;
    language_save($language);
    language_delete('en');
    $this->assertFalse($language_manager->isMultilingual(), 'The test site is monolingual.');

    $this->drupalGet('xx');
    $this->assertText('XX site name');

  }

}
