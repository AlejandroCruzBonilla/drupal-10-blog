<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Plugin\Field\FieldType;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;
use Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemListInterface;

/**
 * Tests the new entity API for the view mode switch field item list.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemList
 */
class ViewModeSwitchItemListTest extends ViewModeSwitchTestBase {

  /**
   * Tests allowed view modes to switch to.
   *
   * @covers ::getAllowedViewModes
   */
  public function testGetAllowedViewModes(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $field_foo = $entity->get($this->fieldFoo->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_bar_baz);

    // Test origin view modes storage settings.
    $this->assertSame([
      'foo1' => 'foo1',
      'foo2' => 'foo2',
    ], $field_foo->getAllowedViewModes());
    $this->assertSame([
      'bar_baz1' => 'bar_baz1',
    ], $field_bar_baz->getAllowedViewModes());
  }

  /**
   * Tests configured origin view modes.
   *
   * @covers ::getOriginViewModes
   */
  public function testGetOriginViewModes(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $field_foo = $entity->get($this->fieldFoo->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_bar_baz);

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
   * Tests whether a view mode switch field is applicable.
   *
   * @covers ::isApplicable
   */
  public function testIsApplicable(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);
    $entity->set($this->fieldBarBaz->getName(), 'bar_baz1');

    $field_foo = $entity->get($this->fieldFoo->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_bar_baz);

    // Test empty field is never applicable.
    $this->assertFalse($field_foo->isApplicable('foo'));
    $this->assertFalse($field_foo->isApplicable('bar'));
    $this->assertFalse($field_foo->isApplicable('baz'));

    // Test field with value is only applicable for configured origin view
    // modes.
    $this->assertTrue($field_bar_baz->isApplicable('bar'));
    $this->assertTrue($field_bar_baz->isApplicable('baz'));
    $this->assertFalse($field_bar_baz->isApplicable('foo'));
  }

  /**
   * Tests whether a view mode switch field is responsible for origin view mode.
   *
   * @covers ::isResponsible
   */
  public function testIsResponsible(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $field_foo = $entity->get($this->fieldFoo->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_bar_baz);

    // Test field is only responsible for configured origin view modes.
    $this->assertTrue($field_foo->isResponsible('foo'));
    $this->assertFalse($field_foo->isResponsible('bar'));
    $this->assertFalse($field_foo->isResponsible('baz'));
    $this->assertTrue($field_bar_baz->isResponsible('bar'));
    $this->assertTrue($field_bar_baz->isResponsible('baz'));
    $this->assertFalse($field_bar_baz->isResponsible('foo'));
  }

  /**
   * Tests using entity fields utilizing the view mode switch field item list.
   *
   * @covers ::getViewMode
   */
  public function testViewModeSwitchItemList(): void {
    // Create entity.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);
    $entity->set($this->fieldFoo->getName(), 'foo1');
    $entity->save();

    // Load saved entity.
    $entity = EntityTest::load($entity->id());
    $this->assertInstanceOf(ContentEntityInterface::class, $entity);

    $field_foo = $entity->get($this->fieldFoo->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_foo);

    $field_bar_baz = $entity->get($this->fieldBarBaz->getName());
    $this->assertInstanceOf(ViewModeSwitchItemListInterface::class, $field_bar_baz);

    // Test saved entity for correct values.
    $this->assertEquals('foo1', $field_foo->value);
    $this->assertEquals('foo1', $field_foo->getViewMode());
    // @todo Ignoring this during PHPStan checks should not be necessary, but
    //   not ignoring the next line leads to the following error: 'Call to
    //   method PHPUnit\Framework\Assert::assertNull() with string will always
    //   evaluate to false.' <- but the 'value' property may also be NULL!
    // @phpstan-ignore-next-line
    $this->assertNull($field_bar_baz->value);
    $this->assertNull($field_bar_baz->getViewMode());
  }

}
