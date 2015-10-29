<?php
/**
 * @file
 */

namespace CW\Drush\Command;

/**
 * Class CommandScaffoldEntityController
 *
 * @package CW\Drush\Command
 *
 * Entity controller scaffolding command.
 *
 * @todo think about adding an interface
 * @todo inject Drush specific operations for better testability
 */
class CommandScaffoldEntityController {

  /**
   * @param string $entityType
   * @param string $bundle
   */
  public static function execute($entityType, $bundle) {
    $fields = array_keys(field_info_instances($entityType, $bundle));
    array_walk($fields, [__CLASS__, 'fieldNameToConst']);
    array_unshift($fields, '  // Field names.');

    drush_print('Copy and paste class code:');
    $namespace = drush_get_option('namespace');
    drush_print(self::getTemplate(join("\n", $fields), $entityType, $bundle, $namespace));
  }

  /**
   * @param string $fieldsRaw
   * @param string $entityType
   * @param string $bundle
   * @param null|string $namespace
   * @return string
   */
  private static function getTemplate($fieldsRaw, $entityType, $bundle, $namespace = NULL) {
    $namespaceRaw = NULL;
    $packageRaw = NULL;
    if ($namespace) {
      $namespaceRaw = "\nnamespace " . $namespace . ";\n";
      $packageRaw = "\n * @package " . $namespace . "\n *";
    }
    $className = self::convertToClassName($bundle);

    return <<<CODE
<?php
/**
 * @file
 */
$namespaceRaw
use CW\Controller\NodeController;

/**
 * Class $className
 * $packageRaw
 * $className entity controller.
 */
class $className extends NodeController {

  // Class $entityType bundle.
  const BUNDLE = '$bundle';

$fieldsRaw

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityBundle() {
    return self::BUNDLE;
  }

}

CODE;
  }

  /**
   * Formats field name into template compliant constant definition.
   *
   * @param string $fieldName
   */
  private static function fieldNameToConst(&$fieldName) {
    $fieldName = '  const ' . strtoupper($fieldName) . ' = \'' . $fieldName . '\';';
  }

  /**
   * Make string class name compatible.
   *
   * Eg.: blog_food -> BlogFood
   *
   * @param string $string
   * @return string
   */
  private static function convertToClassName($string) {
    $string = preg_replace_callback('/[-_ ]./', function ($match) {
      return strtoupper(substr($match[0], 1));
    }, $string);
    return ucfirst($string);
  }

}
