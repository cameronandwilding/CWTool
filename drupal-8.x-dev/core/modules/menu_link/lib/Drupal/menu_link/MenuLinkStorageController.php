<?php

/**
 * @file
 * Contains \Drupal\menu_link\MenuLinkStorageController.
 */

namespace Drupal\menu_link;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\field\FieldInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * Controller class for menu links.
 *
 * This extends the Drupal\entity\DatabaseStorageController class, adding
 * required special handling for menu_link entities.
 */
class MenuLinkStorageController extends DatabaseStorageController implements MenuLinkStorageControllerInterface {

  /**
   * Contains all {menu_router} fields without weight.
   *
   * @var array
   */
  protected static $routerItemFields;

  /**
   * Indicates whether the delete operation should re-parent children items.
   *
   * @var bool
   */
  protected $preventReparenting = FALSE;

  /**
   * The route provider service.
   *
   * @var \Symfony\Cmf\Component\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Overrides DatabaseStorageController::__construct().
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID Service.
   * @param \Symfony\Cmf\Component\Routing\RouteProviderInterface $route_provider
   *   The route provider service.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, UuidInterface $uuid_service, RouteProviderInterface $route_provider) {
    parent::__construct($entity_type, $database, $uuid_service);

    $this->routeProvider = $route_provider;

    if (empty(static::$routerItemFields)) {
      static::$routerItemFields = array_diff(drupal_schema_fields_sql('menu_router'), array('weight'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    // The bundle of menu links being the menu name is not enforced but is the
    // default behavior if no bundle is set.
    if (!isset($values['bundle']) && isset($values['menu_name'])) {
      $values['bundle'] = $values['menu_name'];
    }
    return parent::create($values);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('uuid'),
      $container->get('router.route_provider')
    );
  }

  /**
   * Overrides DatabaseStorageController::buildQuery().
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    // Specify additional fields from the {menu_router} table.
    $query->leftJoin('menu_router', 'm', 'base.link_path = m.path');
    $query->fields('m', static::$routerItemFields);
    return $query;
  }

  /**
   * Overrides DatabaseStorageController::save().
   */
  public function save(EntityInterface $entity) {

    // We return SAVED_UPDATED by default because the logic below might not
    // update the entity if its values haven't changed, so returning FALSE
    // would be confusing in that situation.
    $return = SAVED_UPDATED;

    $transaction = $this->database->startTransaction();
    try {
      // Load the stored entity, if any.
      if (!$entity->isNew() && !isset($entity->original)) {
        $entity->original = entity_load_unchanged($this->entityTypeId, $entity->id());
      }

      if ($entity->isNew()) {
        $entity->mlid = $this->database->insert($this->entityType->getBaseTable())->fields(array('menu_name' => $entity->menu_name))->execute();
        $entity->enforceIsNew();
      }

      // Unlike the save() method from DatabaseStorageController, we invoke the
      // 'presave' hook first because we want to allow modules to alter the
      // entity before all the logic from our preSave() method.
      $this->invokeHook('presave', $entity);
      $entity->preSave($this);

      // If every value in $entity->original is the same in the $entity, there
      // is no reason to run the update queries or clear the caches. We use
      // array_intersect_key() with the $entity as the first parameter because
      // $entity may have additional keys left over from building a router entry.
      // The intersect removes the extra keys, allowing a meaningful comparison.
      if ($entity->isNew() || (array_intersect_key(get_object_vars($entity), get_object_vars($entity->original)) != get_object_vars($entity->original))) {
        $return = drupal_write_record($this->entityType->getBaseTable(), $entity, $this->idKey);

        if ($return) {
          if (!$entity->isNew()) {
            $this->resetCache(array($entity->{$this->idKey}));
            $entity->postSave($this, TRUE);
            $this->invokeHook('update', $entity);
          }
          else {
            $return = SAVED_NEW;
            $this->resetCache();

            $entity->enforceIsNew(FALSE);
            $entity->postSave($this, FALSE);
            $this->invokeHook('insert', $entity);
          }
        }
      }

      // Ignore slave server temporarily.
      db_ignore_slave();
      unset($entity->original);

      return $return;
    }
    catch (\Exception $e) {
      $transaction->rollback();
      watchdog_exception($this->entityTypeId, $e);
      throw new EntityStorageException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPreventReparenting($value = FALSE) {
    $this->preventReparenting = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreventReparenting() {
    return $this->preventReparenting;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUpdatedCustomized(array $router_paths) {
    $query = parent::buildQuery(NULL);
    $query
      ->condition(db_or()
      ->condition('updated', 1)
      ->condition(db_and()
        ->condition('router_path', $router_paths, 'NOT IN')
        ->condition('external', 0)
        ->condition('customized', 1)
        )
      );
    $query_result = $query->execute();

    if ($class = $this->entityType->getClass()) {
      // We provide the necessary arguments for PDO to create objects of the
      // specified entity class.
      // @see \Drupal\Core\Entity\EntityInterface::__construct()
      $query_result->setFetchMode(\PDO::FETCH_CLASS, $class, array(array(), $this->entityTypeId));
    }

    return $query_result->fetchAllAssoc($this->idKey);
  }

  /**
   * {@inheritdoc}
   */
  public function loadModuleAdminTasks() {
    $query = $this->buildQuery(NULL);
    $query
      ->condition('base.link_path', 'admin/%', 'LIKE')
      ->condition('base.hidden', 0, '>=')
      ->condition('base.module', 'system')
      ->condition('m.number_parts', 1, '>')
      ->condition('m.page_callback', 'system_admin_menu_block_page', '<>');
    $ids = $query->execute()->fetchCol(1);

    return $this->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function updateParentalStatus(EntityInterface $entity, $exclude = FALSE) {
    // If plid == 0, there is nothing to update.
    if ($entity->plid) {
      // Check if at least one visible child exists in the table.
      $query = \Drupal::entityQuery($this->entityTypeId);
      $query
        ->condition('menu_name', $entity->menu_name)
        ->condition('hidden', 0)
        ->condition('plid', $entity->plid)
        ->count();

      if ($exclude) {
        $query->condition('mlid', $entity->id(), '<>');
      }

      $parent_has_children = ((bool) $query->execute()) ? 1 : 0;
      $this->database->update('menu_links')
        ->fields(array('has_children' => $parent_has_children))
        ->condition('mlid', $entity->plid)
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findChildrenRelativeDepth(EntityInterface $entity) {
    // @todo Since all we need is a specific field from the base table, does it
    // make sense to convert to EFQ?
    $query = $this->database->select('menu_links');
    $query->addField('menu_links', 'depth');
    $query->condition('menu_name', $entity->menu_name);
    $query->orderBy('depth', 'DESC');
    $query->range(0, 1);

    $i = 1;
    $p = 'p1';
    while ($i <= MENU_MAX_DEPTH && $entity->{$p}) {
      $query->condition($p, $entity->{$p});
      $p = 'p' . ++$i;
    }

    $max_depth = $query->execute()->fetchField();

    return ($max_depth > $entity->depth) ? $max_depth - $entity->depth : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function moveChildren(EntityInterface $entity) {
    $query = $this->database->update($this->entityType->getBaseTable());

    $query->fields(array('menu_name' => $entity->menu_name));

    $p = 'p1';
    $expressions = array();
    for ($i = 1; $i <= $entity->depth; $p = 'p' . ++$i) {
      $expressions[] = array($p, ":p_$i", array(":p_$i" => $entity->{$p}));
    }
    $j = $entity->original->depth + 1;
    while ($i <= MENU_MAX_DEPTH && $j <= MENU_MAX_DEPTH) {
      $expressions[] = array('p' . $i++, 'p' . $j++, array());
    }
    while ($i <= MENU_MAX_DEPTH) {
      $expressions[] = array('p' . $i++, 0, array());
    }

    $shift = $entity->depth - $entity->original->depth;
    if ($shift > 0) {
      // The order of expressions must be reversed so the new values don't
      // overwrite the old ones before they can be used because "Single-table
      // UPDATE assignments are generally evaluated from left to right"
      // @see http://dev.mysql.com/doc/refman/5.0/en/update.html
      $expressions = array_reverse($expressions);
    }
    foreach ($expressions as $expression) {
      $query->expression($expression[0], $expression[1], $expression[2]);
    }

    $query->expression('depth', 'depth + :depth', array(':depth' => $shift));
    $query->condition('menu_name', $entity->original->menu_name);
    $p = 'p1';
    for ($i = 1; $i <= MENU_MAX_DEPTH && $entity->original->{$p}; $p = 'p' . ++$i) {
      $query->condition($p, $entity->original->{$p});
    }

    $query->execute();

    // Check the has_children status of the parent, while excluding this item.
    $this->updateParentalStatus($entity->original, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function countMenuLinks($menu_name) {
    $query = \Drupal::entityQuery($this->entityTypeId);
    $query
      ->condition('menu_name', $menu_name)
      ->count();
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getParentFromHierarchy(EntityInterface $entity) {
    $parent_path = $entity->link_path;
    do {
      $parent = FALSE;
      $parent_path = substr($parent_path, 0, strrpos($parent_path, '/'));

      $query = \Drupal::entityQuery($this->entityTypeId);
      $query
        ->condition('mlid', $entity->id(), '<>')
        ->condition('module', 'system')
        // We always respect the link's 'menu_name'; inheritance for router
        // items is ensured in _menu_router_build().
        ->condition('menu_name', $entity->menu_name)
        ->condition('link_path', $parent_path);

      $result = $query->execute();
      // Only valid if we get a unique result.
      if (count($result) == 1) {
        $parent = $this->load(reset($result));
      }
    } while ($parent === FALSE && $parent_path);

    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function createFromDefaultLink(array $item) {
    // Suggested items are disabled by default.
    $item += array(
      'type' => MENU_NORMAL_ITEM,
      'hidden' => 0,
      'options' => empty($item['description']) ? array() : array('attributes' => array('title' => $item['description'])),
    );
    if ($item['type'] == MENU_SUGGESTED_ITEM) {
      $item['hidden'] = 1;
    }
    // Note, we set this as 'system', so that we can be sure to distinguish all
    // the menu links generated automatically from hook_menu_link_defaults().
    $item['module'] = 'system';
    return $this->create($item);
  }

}
