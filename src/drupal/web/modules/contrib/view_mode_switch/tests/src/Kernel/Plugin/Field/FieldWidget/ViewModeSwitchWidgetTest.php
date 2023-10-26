<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;

/**
 * Tests the view mode switch widget.
 *
 * @group view_mode_switch
 */
class ViewModeSwitchWidgetTest extends ViewModeSwitchTestBase {

  /**
   * The entity form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   */
  protected $entityFormDisplay;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display');

    // Create default entity form display.
    $entity_form_display = $storage->create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ]);
    $this->assertInstanceOf(EntityFormDisplayInterface::class, $entity_form_display);
    $this->entityFormDisplay = $entity_form_display
      ->setComponent($this->fieldFoo->getName(), [
        'type' => 'view_mode_switch',
      ])
      ->setComponent($this->fieldBarBaz->getName(), [
        'type' => 'view_mode_switch',
      ]);
    $this->entityFormDisplay->save();
  }

  /**
   * Tests the widget's allowed/selectable view mode options.
   */
  public function testAllowedViewModes(): void {
    $entity_form_builder = $this->container->get('entity.form_builder');

    // Get field names.
    $field_foo = $this->fieldFoo->getName();
    $field_bar_baz = $this->fieldBarBaz->getName();

    // Create entity and load create form.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $form = $entity_form_builder->getForm($entity);

    $options_foo = $form[$field_foo]['widget'][0]['value']['#options'];
    $options_bar_baz = $form[$field_bar_baz]['widget'][0]['value']['#options'];

    // Test available options in create form.
    $this->assertSame(['', 'foo1', 'foo2'], array_keys($options_foo));
    $this->assertSame(['', 'bar_baz1'], array_keys($options_bar_baz));

    // Test empty options in create form.
    $this->assertEquals($this->t('- No change -'), $options_bar_baz['']);
    $this->assertEquals($this->t('- Select -'), $options_foo['']);

    // Create/save entity and load edit form.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $entity->set($field_foo, 'foo1');
    $entity->save();
    $form = $entity_form_builder->getForm($entity);

    $options_foo = $form[$field_foo]['widget'][0]['value']['#options'];
    $options_bar_baz = $form[$field_bar_baz]['widget'][0]['value']['#options'];

    // Test empty options in edit form.
    $this->assertEquals($this->t('- No change -'), $options_bar_baz['']);
    $this->assertArrayNotHasKey('', $options_foo);
  }

}
