<?php
/**
 * @file
 */

namespace Controller;

use CW\Controller\UserController;
use CW\Test\TestCase;

class UserControllerTest extends TestCase {

  const LANGUAGE_NONE = 'und';

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var UserController
   */
  protected $controller;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  protected $entityType;

  protected $entityId;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $drupalAdapterMock;

  private $fullEntity;

  public function setUp() {
    parent::setUp();

    $this->objectHandlerMock = $this->getMock('CW\Model\DrupalEntityHandler');
    $this->loggerMock = $this->getMock('Psr\Log\AbstractLogger');
    $this->entityType = UserController::getClassEntityType();
    $this->entityId = self::randomInt();

    $this->drupalAdapterMock = $this->getMock('CW\Adapter\DrupalUserAdapter');

    UserController::setDrupalAdapter($this->drupalAdapterMock);
    $this->controller = new UserController($this->loggerMock, $this->objectHandlerMock, $this->entityType, $this->entityId);
    syslog(LOG_ERR, 'new');

    $this->fullEntity = (object) [
      'uid' => $this->entityId,
      'name' => 'johndoe',
      'mail' => 'johndoe@example.com',
    ];
  }

  public function testMail() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertEquals('johndoe@example.com', $this->controller->getMail());
  }

  public function testIsCurrentYes() {
    $this->drupalAdapterMock
      ->expects($this->once())
      ->method('getGlobalUserObject')
      ->willReturn((object) [
        'uid' => $this->entityId,
      ]);

    $this->assertTrue($this->controller->isCurrent());
  }

  public function testIsCurrentNo() {
    $this->drupalAdapterMock
      ->expects($this->once())
      ->method('getGlobalUserObject')
      ->willReturn((object) [
        'uid' => $this->entityId + 1,
      ]);

    $this->assertFalse($this->controller->isCurrent());
  }

  public function testCurrentUID() {
    $uid = rand(0, PHP_INT_MAX);
    $this->drupalAdapterMock
      ->expects($this->once())
      ->method('getGlobalUserObject')
      ->willReturn((object) [
        'uid' => $uid,
      ]);

    $this->assertEquals($uid, $this->controller->currentUID());
  }

  public function testIsAdminYes() {
    $adminCtrl = new UserController($this->loggerMock, $this->objectHandlerMock, $this->entityType, UserController::UID_ADMIN);
    $this->assertTrue($adminCtrl->isAdmin());
  }

  public function testIsAdminNo() {
    $adminCtrl = new UserController($this->loggerMock, $this->objectHandlerMock, $this->entityType, UserController::UID_ADMIN + rand(1, 1000));
    $this->assertFalse($adminCtrl->isAdmin());
  }

  public function testLogin() {
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->drupalAdapterMock
      ->expects($this->once())
      ->method('login')
      ->with($this->fullEntity, $this->loggerMock);

    $this->controller->login();
  }

  public function testHasRole() {
    $this->fullEntity->roles = ['foo' => 'foo'];

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->assertTrue($this->controller->hasRole('foo'));
    $this->assertFalse($this->controller->hasRole('Foo'));
    $this->assertFalse($this->controller->hasRole('bar'));
  }

  public function testUserEntityIncomplete() {
    $account = (object) [
      'uid' => $this->entityId,
      // This is a sign that the user object is used from global.
      'sid' => 'foobar123',
    ];

    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->willReturn($this->fullEntity);

    $this->controller->setEntity($account);

    $this->assertObjectHasAttribute('name', $this->controller->entity());
    $this->assertObjectHasAttribute('mail', $this->controller->entity());
    $this->assertObjectNotHasAttribute('sid', $this->controller->entity());
  }

  public function testGetPath() {
    $this->assertEquals('user/' . $this->entityId, $this->controller->getPath());
  }

}
