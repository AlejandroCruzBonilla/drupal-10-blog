<?php

namespace Drupal\Tests\view_mode_switch\Kernel;

use Drupal\entity_test\Entity\EntityTest;

/**
 * Tests view mode switches initiated by view mode switch field values.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\ViewModeSwitch
 */
class ViewModeSwitchTest extends ViewModeSwitchTestBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Field: foo1.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $fieldFoo1;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog'];

  /**
   * The view mode switch.
   *
   * @var \Drupal\view_mode_switch\ViewModeSwitchInterface
   */
  protected $viewModeSwitch;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('dblog', ['watchdog']);

    $this->connection = $this->container->get('database');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->viewModeSwitch = $this->container->get('view_mode_switch');

    // Create an optional view mode switch field for 'foo1'.
    $field_foo1_origin_view_modes = ['foo1'];
    $field_foo1_allowed_view_modes = ['foo', 'bar'];
    $this->fieldFoo1 = $this->createViewModeSwitchField('foo1', $field_foo1_origin_view_modes, $field_foo1_allowed_view_modes);
  }

  /**
   * Tests determination of view mode to switch to.
   *
   * @covers ::doGetViewModeToSwitchTo
   * @covers ::getViewModeToSwitchTo
   */
  public function testGetViewModeToSwitchTo(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    // View mode switch: foo -> foo1.
    $entity->set($this->fieldFoo->getName(), 'foo1');
    // View mode switch: foo1 -> bar.
    $entity->set($this->fieldFoo1->getName(), 'bar');
    // View mode switch: bar -> bar_baz1.
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    // Test resulting view mode.
    $this->assertEquals('bar_baz1', $this->viewModeSwitch->getViewModeToSwitchTo($entity, 'foo'));
  }

  /**
   * Tests the view mode switch recursion detection.
   *
   * @covers ::doGetViewModeToSwitchTo
   * @covers ::getViewModeToSwitchTo
   */
  public function testGetViewModeToSwitchToRecursionDetection(): void {
    // Create entity resulting in view mode switch recursion.
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entity = EntityTest::create([
      'id' => 1,
      'name' => $this->randomString(),
    ]);
    // View mode switch: foo -> foo1.
    $entity->set($this->fieldFoo->getName(), 'foo1');
    // View mode switch: foo1 -> foo (results in recursion).
    $entity->set($this->fieldFoo1->getName(), 'foo');

    // Test recursion detection.
    $this->assertEquals('foo1', $this->viewModeSwitch->getViewModeToSwitchTo($entity, 'foo'));

    // Ensure that the detected recursion is logged.
    /** @var \Drupal\Core\Database\StatementInterface $query */
    $query = $this->connection
      ->select('watchdog', 'w')
      ->fields('w', [
        'message',
        'variables',
        'link',
      ])
      ->orderBy('wid', 'DESC')
      ->range(0, 1)
      ->execute();
    $log = $query->fetch();

    // Test recursion detection log message.
    $this->assertEquals('Recursion detected when trying to switch %origin_view_mode view mode via %view_mode_switches.', $log->message);

    // Test recursion detection log message context.
    $variables = unserialize($log->variables, [
      'allowed_classes' => FALSE,
    ]);
    $this->assertIsArray($variables);
    $this->assertEquals('foo', $variables['%origin_view_mode']);
    $this->assertEquals('foo › foo1 › foo', $variables['%view_mode_switches']);
    $this->assertEquals((string) $entity->toLink('View')->toString(), $log->link);
  }

  /**
   * Tests the view mode alter behavior of view mode switch fields.
   *
   * @covers ::doGetViewModeToSwitchTo
   * @covers ::getViewModeToSwitchTo
   */
  public function testViewModeAlter(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    // View mode switch: foo -> foo1.
    $entity->set($this->fieldFoo->getName(), 'foo1');
    // View mode switch: foo1 -> bar.
    $entity->set($this->fieldFoo1->getName(), 'bar');
    // View mode switch: bar -> bar_baz1.
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    // Build entity.
    $build = $this->entityTypeManager
      ->getViewBuilder('entity_test')
      ->view($entity, 'foo');

    // Test resulting view mode.
    $this->assertEquals('bar_baz1', $build['#view_mode']);
  }

}
