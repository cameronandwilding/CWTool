<?php
/**
 * @file
 * Contains \Drupal\rdf\Tests\Field\TaxonomyTermReferenceRdfaTest.
 */

namespace Drupal\rdf\Tests\Field;

use Drupal\rdf\Tests\Field\FieldRdfaTestBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Language\Language;

/**
 * Tests the RDFa output of the taxonomy term reference field formatter.
 */
class TaxonomyTermReferenceRdfaTest extends FieldRdfaTestBase {

  /**
   * {@inheritdoc}
   */
  protected $fieldType = 'taxonomy_term_reference';

  /**
   * The term for testing.
   *
   * @var \Drupal\taxonomy\Entity\Term
   */
  protected $term;

  /**
   * The URI of the term for testing.
   *
   * @var string
   */
  protected $termUri;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('taxonomy', 'options', 'text', 'filter');

  public static function getInfo() {
    return array(
      'name' => 'Field formatter: taxonomy term reference',
      'description' => 'Tests RDFa output by taxonomy term reference field formatters.',
      'group' => 'RDF',
    );
  }

  public function setUp() {
    parent::setUp();

    $this->installSchema('taxonomy', array('taxonomy_term_data', 'taxonomy_term_hierarchy'));

    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $vocabulary->save();

    entity_create('field_entity', array(
      'name' => $this->fieldName,
      'entity_type' => 'entity_test',
      'type' => 'taxonomy_term_reference',
      'cardinality' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $vocabulary->id(),
            'parent' => 0,
          ),
        ),
      ),
    ))->save();
    entity_create('field_instance', array(
      'entity_type' => 'entity_test',
      'field_name' => $this->fieldName,
      'bundle' => 'entity_test',
    ))->save();

    $this->term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $vocabulary->id(),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
    ));
    $this->term->save();

    // Add the mapping.
    $mapping = rdf_get_mapping('entity_test', 'entity_test');
    $mapping->setFieldMapping($this->fieldName, array(
      'properties' => array('schema:about'),
    ))->save();

    // Set up test values.
    $this->entity = entity_create('entity_test');
    $this->entity->{$this->fieldName}->target_id = $this->term->id();
    $this->entity->save();
    $this->uri = $this->getAbsoluteUri($this->entity);
  }

  /**
   * Tests the plain formatter.
   */
  public function testPlainFormatter() {
    $this->assertFormatterRdfa('taxonomy_term_reference_plain', 'http://schema.org/about', $this->term->label(), 'literal');
  }

  /**
   * Tests the link formatter.
   */
  public function testLinkFormatter() {
    $term_uri = $this->getAbsoluteUri($this->term);
    $this->assertFormatterRdfa('taxonomy_term_reference_link', 'http://schema.org/about', $term_uri, 'uri');
  }

}
