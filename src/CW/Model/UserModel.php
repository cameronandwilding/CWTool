<?php
/**
 * @file
 */

namespace CW\Model;

class UserModel extends EntityModel {

  const USER_CURRENT = -1;

  const UID_ANONYMOUS = 0;

  const UID_ADMIN = 1;

  const STATE_ACTIVE = 1;

  const STATE_BLOCKED = 0;

  const ROLE_AUTHENTICATED_USER = 'authenticated user';

}
