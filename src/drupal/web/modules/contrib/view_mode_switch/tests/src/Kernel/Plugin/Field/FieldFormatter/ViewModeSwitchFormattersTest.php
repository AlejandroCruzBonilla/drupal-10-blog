<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;

/**
 * Tests the 'View mode switch' field type formatters.
 *
 * @group view_mode_switch
 * @see \Drupal\view_mode_switch\Plugin\Field\FieldFormatter\ViewModeSwitchDefaultFormatter
 * @see \Drupal\view_mode_switch\Plugin\Field\FieldFormatter\ViewModeSwitchMachineNameFormatter
 */
class ViewModeSwitchFormattersTest extends ViewModeSwitchTestBase {

  /**
   * Tests the built-in formatters.
   */
  public function testFormatter(): void {
    $entity = EntityTest::create();

    $field_name_foo = $this->fieldFoo->getName();
    $field_name_bar_baz = $this->fieldBarBaz->getName();

    $expected_foo = 'foo1';
    $expected_bar_baz = 'bar_baz1';

    $entity->set($field_name_foo, $expected_foo);
    $entity->set($field_name_bar_baz, $expected_bar_baz);

    /** @var \Drupal\Core\Field\FieldItemListInterface $items_foo */
    $items_foo = $entity->get($field_name_foo);

    /** @var \Drupal\Core\Field\FieldItemListInterface $items_bar_baz */
    $items_bar_baz = $entity->get($field_name_bar_baz);

    // Test 'Default' formatter.
    $build = $items_foo->view();
    $this->assertEquals('view_mode_switch_default', $build['#formatter'], 'Ensure to fall back to the default formatter.');
    $this->assertEquals($expected_foo . ' label', $build[0]['#markup']);

    // Test 'Machine name' formatter.
    $build = $items_bar_baz->view(['type' => 'view_mode_switch_machine_name']);
    $this->assertEquals('view_mode_switch_machine_name', $build['#formatter'], 'The chosen formatter is used.');
    $this->assertEquals($expected_bar_baz, (string) $build[0]['#markup']);
  }

}
