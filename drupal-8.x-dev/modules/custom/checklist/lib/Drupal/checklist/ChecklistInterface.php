<?php

/**
 * @file
 * Contains \Drupal\checklist\ChecklistInterface.
 */

namespace Drupal\checklist;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a checklistitem entity.
 */
interface ChecklistInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the checklist type.
   *
   * @return string
   *   The checklist type.
   */
  public function getType();

  /**
   * Returns the checklist title.
   *
   * @return string
   *   Title of the checklist.
   */
  public function getTitle();

  /**
   * Sets the checklist title.
   *
   * @param string $title
   *   The checklist title.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setTitle($title);

  /**
   * Returns the checklist creation timestamp.
   *
   * @return int
   *   Creation timestamp of the checklist.
   */
  public function getCreatedTime();

  /**
   * Sets the checklist creation timestamp.
   *
   * @param int $timestamp
   *   The checklist creation timestamp.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the checklist promotion status.
   *
   * @return bool
   *   TRUE if the checklist is promoted.
   */
  public function isPromoted();

  /**
   * Sets the checklist promoted status.
   *
   * @param bool $promoted
   *   TRUE to set this checklist to promoted, FALSE to set it to not promoted.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setPromoted($promoted);

  /**
   * Returns the checklist sticky status.
   *
   * @return bool
   *   TRUE if the checklist is sticky.
   */
  public function isSticky();

  /**
   * Sets the checklist sticky status.
   *
   * @param bool $sticky
   *   TRUE to set this checklist to sticky, FALSE to set it to not sticky.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setSticky($sticky);

  /**
   * Returns the checklist published status indicator.
   *
   * Unpublished checklists are only visible to their authors and to administrators.
   *
   * @return bool
   *   TRUE if the checklist is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a checklist..
   *
   * @param bool $published
   *   TRUE to set this checklist to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setPublished($published);

  /**
   * Returns the checklist revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the checklist revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Returns the checklist revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the checklist revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\checklist\ChecklistInterface
   *   The called checklist entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Prepares the langcode for a checklist.
   *
   * @return string
   *   The langcode for this checklist.
   */
  public function prepareLangcode();

}
