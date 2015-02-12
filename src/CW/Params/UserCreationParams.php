<?php
/**
 * @file
 */

namespace CW\Params;
use Exception;

/**
 * Class UserCreationParams
 * @package CW\Params
 */
class UserCreationParams extends EntityCreationParams {

  private $userName;

  /**
   * @var array
   */
  private $roles;

  /**
   * @var null
   */
  private $email;

  /**
   * @var null
   */
  private $password;

  public function __construct($userName, $roles = array(), $email = NULL, $password = NULL, array $extraAttributes = array()) {
    parent::__construct($extraAttributes);

    $this->userName = $userName;
    $this->roles = $roles;
    $this->email = $email;
    $this->password = $password;
  }

  /**
   * @return mixed
   */
  public function getUserName() {
    return $this->userName;
  }

  /**
   * @param mixed $userName
   */
  public function setUserName($userName) {
    $this->userName = $userName;
  }

  /**
   * @return array
   */
  public function getRoles() {
    return $this->roles;
  }

  /**
   * @param array $roles
   */
  public function setRoles($roles) {
    $this->roles = $roles;
  }

  public function addRole($roleName) {
    static $drupal_roles;

    if (!isset($drupal_roles)) {
      $drupal_roles = user_roles(TRUE);
    }

    if (!in_array($roleName, $drupal_roles)) {
      throw new Exception('Role not found: ' . $roleName);
    }

    $reverse_roles = array_flip($drupal_roles);
    $this->roles[$reverse_roles[$roleName]] = $roleName;
  }

  /**
   * @return null
   */
  public function getEmail() {
    return empty($this->email) ? md5(microtime(TRUE)) . '@example.com' : $this->email;
  }

  /**
   * @param null $email
   */
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
   * @return null
   */
  public function getPassword() {
    return empty($this->password) ? user_password() : $this->password;
  }

  /**
   * @param null $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }

}
