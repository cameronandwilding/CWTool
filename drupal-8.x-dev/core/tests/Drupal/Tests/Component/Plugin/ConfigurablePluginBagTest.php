<?php

/**
 * @file
 * Contains \Drupal\Tests\Component\Plugin\ConfigurablePluginBagTest.
 */

namespace Drupal\Tests\Component\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Tests the default plugin bag with configurable plugins.
 *
 * @see \Drupal\Component\Plugin\ConfigurablePluginInterface
 * @see \Drupal\Component\Plugin\DefaultPluginBag
 *
 * @group Drupal
 * @group Drupal_Plugin
 */
class ConfigurablePluginBagTest extends PluginBagTestBase {

  /**
   * Stores all setup plugin instances.
   *
   * @var \Drupal\Component\Plugin\ConfigurablePluginInterface[]
   */
  protected $pluginInstances;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Configurable plugin bag',
      'description' => 'Tests the plugin bag with configurable plugins.',
      'group' => 'Plugin API',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginMock($plugin_id, array $definition) {
    return new TestConfigurablePlugin($this->config[$plugin_id], $plugin_id, $definition);
  }

  /**
   * Tests the getConfiguration() method with configurable plugins.
   */
  public function testConfigurableGetConfiguration() {
    $this->setupPluginBag($this->exactly(3));
    $config = $this->defaultPluginBag->getConfiguration();
    $this->assertSame($this->config, $config);
  }

  /**
   * Tests the setConfiguration() method with configurable plugins.
   */
  public function testConfigurableSetConfiguration() {
    $this->setupPluginBag($this->exactly(3));
    $this->defaultPluginBag->getConfiguration();
    $this->defaultPluginBag->setConfiguration('apple', array('value' => 'pineapple'));

    $expected = $this->config;
    $expected['apple'] = array('value' => 'pineapple');
    $config = $this->defaultPluginBag->getConfiguration();
    $this->assertSame($expected, $config);
    $plugin = $this->pluginInstances['apple'];
    $this->assertSame($expected['apple'], $plugin->getConfiguration());
  }

}

class TestConfigurablePlugin extends PluginBase implements ConfigurablePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

}
