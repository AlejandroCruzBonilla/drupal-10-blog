<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Plugin\Field\FieldType;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface;

/**
 * Tests the new entity API for the view mode switch field type.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItem
 */
class ViewModeSwitchItemTest extends ViewModeSwitchTestBase {

  /**
   * Tests view mode switch field configuration dependencies.
   *
   * @covers ::calculateDependencies
   */
  public function testCalculateDependencies(): void {
    // Test dependencies of 'foo' view mode switch field.
    $dependencies = $this->fieldFoo->getDependencies();
    $this->assertContains('view_mode_switch', $dependencies['module']);
    $this->assertContains($this->viewModeFoo1->getConfigDependencyName(), $dependencies[$this->viewModeFoo1->getConfigDependencyKey()]);
    $this->assertContains($this->viewModeFoo2->getConfigDependencyName(), $dependencies[$this->viewModeFoo2->getConfigDependencyKey()]);
    $this->assertNotContains($this->viewModeFoo->getConfigDependencyName(), $dependencies[$this->viewModeFoo->getConfigDependencyKey()]);

    // Test dependencies of 'bar_baz' view mode switch field.
    $dependencies = $this->fieldBarBaz->getDependencies();
    $this->assertContains('view_mode_switch', $dependencies['module']);
    $this->assertContains('view_mode_switch', $dependencies['module']);
    $this->assertContains($this->viewModeBarBaz1->getConfigDependencyName(), $dependencies[$this->viewModeBarBaz1->getConfigDependencyKey()]);
    $this->assertNotContains($this->viewModeBar->getConfigDependencyName(), $dependencies[$this->viewModeBar->getConfigDependencyKey()]);
    $this->assertNotContains($this->viewModeBaz->getConfigDependencyName(), $dependencies[$this->viewModeBaz->getConfigDependencyKey()]);
  }

  /**
   * Tests allowed view modes to switch to.
   *
   * @covers ::getAllowedViewModes
   */
  public function testGetAllowedViewModes(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $field_foo */
    $field_foo = $entity->get($this->fieldFoo->getName())->first();

    /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $field_bar_baz */
    $field_bar_baz = $entity->get($this->fieldBarBaz->getName())->first();

    // Test view modes to switch to storage settings.
    $this->assertSame([
      'foo1' => 'foo1',
      'foo2' => 'foo2',
    ], $field_foo->getAllowedViewModes());
    $this->assertSame([
      'bar_baz1' => 'bar_baz1',
    ], $field_bar_baz->getAllowedViewModes());
  }

  /**
   * Tests possible view mode switch field options.
   *
   * @covers ::getPossibleOptions
   */
  public function testGetPossibleOptions(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);

    $options_provider = $this->fieldFoo->getFieldStorageDefinition()->getOptionsProvider($this->fieldFoo->getName(), $entity);
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $options_provider);

    // Test possible options.
    $this->assertSame([
      'default',
      'bar',
      'bar_baz1',
      'baz',
      'foo',
      'foo1',
      'foo2',
    ], array_keys($options_provider->getPossibleOptions()));
  }

  /**
   * Tests possible view mode switch field values.
   *
   * @covers ::getPossibleOptions
   */
  public function testGetPossibleValues(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);

    $options_provider = $this->fieldFoo->getFieldStorageDefinition()->getOptionsProvider($this->fieldFoo->getName(), $entity);
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $options_provider);

    // Test possible values.
    $this->assertSame([
      'bar',
      'bar_baz1',
      'baz',
      'default',
      'foo',
      'foo1',
      'foo2',
    ], $options_provider->getPossibleValues());
  }

  /**
   * Tests settable view mode switch field options.
   *
   * @covers ::getSettableOptions
   */
  public function testGetSettableOptions(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $options_provider = $this->fieldFoo->getFieldStorageDefinition()->getOptionsProvider($this->fieldFoo->getName(), $entity);
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $options_provider);

    // Test allowed settable options.
    $this->assertSame([
      'foo1',
      'foo2',
    ], array_keys($options_provider->getSettableOptions()));

    // Test all settable options.
    $this->assertSame([
      'bar',
      'bar_baz1',
      'baz',
      'foo1',
      'foo2',
    ], array_keys($options_provider->getSettableOptions(NULL, FALSE)));
  }

  /**
   * Tests settable view mode switch field values.
   *
   * @covers ::getSettableValues
   */
  public function testGetSettableValues(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $options_provider = $this->fieldFoo->getFieldStorageDefinition()->getOptionsProvider($this->fieldFoo->getName(), $entity);
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $options_provider);

    // Test allowed settable values.
    $this->assertSame([
      'foo1',
      'foo2',
    ], $options_provider->getSettableValues());

    // Test all settable values.
    $this->assertSame([
      'bar',
      'bar_baz1',
      'baz',
      'foo1',
      'foo2',
    ], $options_provider->getSettableValues(NULL, FALSE));
  }

  /**
   * Tests whether a view mode switch field is applicable.
   *
   * @covers ::isApplicable
   */
  public function testIsApplicable(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $entity->set($this->fieldFoo->getName(), 'foo1');

    $field_foo = $entity->get($this->fieldFoo->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_foo);

    // Test field with value is only applicable for configured origin view
    // modes.
    $this->assertTrue($field_foo->isApplicable('foo'));
    $this->assertFalse($field_foo->isApplicable('bar'));
    $this->assertFalse($field_foo->isApplicable('baz'));
  }

  /**
   * Tests whether a view mode switch field is responsible for origin view mode.
   *
   * @covers ::isResponsible
   */
  public function testIsResponsible(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    $field_foo = $entity->get($this->fieldFoo->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_bar_baz);

    // Test field is only responsible for configured origin view modes.
    $this->assertTrue($field_foo->isResponsible('foo'));
    $this->assertFalse($field_foo->isResponsible('bar'));
    $this->assertFalse($field_foo->isResponsible('baz'));
    $this->assertTrue($field_bar_baz->isResponsible('bar'));
    $this->assertTrue($field_bar_baz->isResponsible('baz'));
    $this->assertFalse($field_bar_baz->isResponsible('foo'));
  }

  /**
   * Tests configured origin view modes.
   *
   * @covers ::getOriginViewModes
   */
  public function testGetOriginViewModes(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    $field_foo = $entity->get($this->fieldFoo->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_bar_baz);

    // Test origin view modes storage settings.
    $this->assertSame([
      'foo' => 'foo',
    ], $field_foo->getOriginViewModes());
    $this->assertSame([
      'bar' => 'bar',
      'baz' => 'baz',
    ], $field_bar_baz->getOriginViewModes());
  }

  /**
   * Tests view mode switch field configuration dependency removal behavior.
   *
   * @covers ::onDependencyRemoval
   */
  public function testOnDependencyRemoval(): void {
    // Test dependency removal behavior on 'foo' field when allowed 'foo1' view
    // mode is deleted (should be removed from config dependencies).
    $dependencies = $this->fieldFoo->getDependencies();
    $this->assertContains($this->viewModeFoo1->getConfigDependencyName(), $dependencies[$this->viewModeFoo1->getConfigDependencyKey()]);
    $this->viewModeFoo1->delete();
    $field_config = FieldConfig::load($this->fieldFoo->id());
    $this->assertInstanceOf(FieldConfigInterface::class, $field_config);
    $dependencies = $field_config->getDependencies();
    $this->assertNotContains($this->viewModeFoo1->getConfigDependencyName(), $dependencies[$this->viewModeFoo1->getConfigDependencyKey()]);
    $this->assertContains($this->viewModeFoo2->getConfigDependencyName(), $dependencies[$this->viewModeFoo2->getConfigDependencyKey()]);

    // Test dependency removal behavior on 'foo' field when allowed 'foo2' view
    // mode is deleted, too (field should be deleted due to no allowed view
    // modes anymore).
    $dependencies = $this->fieldFoo->getDependencies();
    $this->assertContains($this->viewModeFoo2->getConfigDependencyName(), $dependencies[$this->viewModeFoo2->getConfigDependencyKey()]);
    $this->viewModeFoo2->delete();
    $this->assertNull(FieldConfig::load($this->fieldFoo->id()));
    $field_storage_definition = $this->fieldFoo->getFieldStorageDefinition();
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_definition);
    $this->assertNull(FieldStorageConfig::load($field_storage_definition->id()));
  }

  /**
   * Tests the allowed view modes validation constraint.
   *
   * @covers ::validate
   */
  public function testValidation(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    // Test valid value.
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $violations = $entity->validate();
    $this->assertEquals(0, count($violations), 'No violations when validating a view mode switch field with valid value.');

    // Test invalid value.
    $entity->set($this->fieldFoo->getName(), 'invalid');
    $violations = $entity->validate();
    $this->assertEquals(1, count($violations), 'Violations found when validating a view mode switch field with invalid value.');
    $this->assertEquals($this->fieldFoo->getName() . '.0', $violations[0]->getPropertyPath());
    $this->assertEquals('The value you selected is not a valid choice.', $violations[0]->getMessage());
  }

  /**
   * Tests using entity fields of the view mode switch field type.
   *
   * @covers ::getViewMode
   */
  public function testViewModeSwitchItem(): void {
    // Create entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $entity->save();

    // Load saved entity.
    $entity = EntityTest::load($entity->id());
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $field_foo = $entity->get($this->fieldFoo->getName())->first();
    $this->assertInstanceOf(ViewModeSwitchItemInterface::class, $field_foo);

    // Test saved entity for correct values.
    $this->assertEquals('foo1', !empty($field_foo->value) ? $field_foo->value : '');
    $this->assertEquals('foo1', $field_foo->getViewMode());
  }

}
