<?php

namespace Drupal\Tests\view_mode_switch\Unit\Entity;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\view_mode_switch\Entity\EntityFieldManagerInterface;
use Drupal\view_mode_switch\Entity\EntityViewModeDeleteFormHelper;
use Drupal\view_mode_switch\ViewModeHelper;

/**
 * Tests the entity view mode delete form helper service.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Entity\EntityViewModeDeleteFormHelper
 */
class EntityViewModeDeleteFormHelperTest extends UnitTestCase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The entity view mode delete form helper service.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityViewModeDeleteFormHelperInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityViewModeDeleteFormHelper;

  /**
   * A test view mode.
   *
   * @var \Drupal\Core\Entity\EntityViewModeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewMode;

  /**
   * The view mode helper service.
   *
   * @var \Drupal\view_mode_switch\ViewModeHelperInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeHelper;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Create required service mocks.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->viewModeSwitchEntityFieldManager = $this->createMock(EntityFieldManagerInterface::class);
    $this->viewMode = $this->createMock(EntityViewModeInterface::class);

    // Create view mode helper service mock.
    $this->viewModeHelper = $this->getMockBuilder(ViewModeHelper::class)
      ->onlyMethods([])
      ->setConstructorArgs([
        $this->viewModeSwitchEntityFieldManager,
        $this->entityTypeManager,
      ])
      ->getMock();

    // Create entity view mode delete form helper service mock.
    $this->entityViewModeDeleteFormHelper = $this->getMockBuilder(EntityViewModeDeleteFormHelper::class)
      ->onlyMethods([])
      ->setConstructorArgs([
        $this->viewModeHelper,
        $this->viewModeSwitchEntityFieldManager,
        $this->entityTypeManager,
      ])
      ->getMock();
  }

  /**
   * Data provider for testing field storage updates in view mode delete form.
   *
   * @return array
   *   The field storage label data to test.
   */
  public function dataProviderAlter(): array {
    return [
      [NULL],
      ['Entity test'],
    ];
  }

  /**
   * Tests field storage updates appear in view mode delete form.
   *
   * @covers ::alter
   *
   * @dataProvider dataProviderAlter
   */
  public function testAlter(?string $field_storage_label): void {
    $form = [];

    $entity_type_id = 'entity_test';
    $entity_type_label = 'Entity test';
    $field_storage_config_id = 'field_storage_config_id';
    $view_mode_name = 'view_mode_test';

    // Prepare view mode.
    $this->viewMode->expects($this->once())
      ->method('id')
      ->willReturn($entity_type_id . '.' . $view_mode_name);

    // Prepare form object.
    /** @var \Drupal\Core\Entity\EntityFormInterface|\PHPUnit\Framework\MockObject\MockObject $form_object */
    $form_object = $this->createMock(EntityFormInterface::class);

    $form_object->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->viewMode);

    $form_object->expects($this->once())
      ->method('getOperation')
      ->willReturn('delete');

    // Prepare form state.
    /** @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject $form_state */
    $form_state = $this->createMock(FormStateInterface::class);

    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);

    // Prepare entity type.
    $entity_type = $this->createMock(EntityTypeInterface::class);

    $entity_type->expects($this->once())
      ->method('getLabel')
      ->willReturn($entity_type_label);

    // Prepare field storage config.
    $field_storage_config = $this->createMock(FieldStorageConfigInterface::class);

    $field_storage_config->expects($this->once())
      ->method('getEntityTypeId')
      ->willReturn($entity_type_id);

    $field_storage_config->expects($this->exactly($field_storage_label ? 1 : 2))
      ->method('id')
      ->willReturn($field_storage_config_id);

    $field_storage_config->expects($this->once())
      ->method('label')
      ->willReturn($field_storage_label);

    // Prepare entity type manager.
    $this->entityTypeManager->expects($this->once())
      ->method('getDefinition')
      ->with($entity_type_id)
      ->willReturn($entity_type);

    // Prepare view mode switch entity field manager.
    $this->viewModeSwitchEntityFieldManager->expects($this->once())
      ->method('getFieldStorageDefinitionsUsingOriginViewMode')
      ->with($view_mode_name)
      ->willReturn([$field_storage_config]);

    $this->entityViewModeDeleteFormHelper->alter($form, $form_state);
    $this->assertTrue(isset($form['entity_updates']['#access']));
    $this->assertTrue(isset($form['entity_updates'][$entity_type_id]));
    $this->assertEquals('item_list', $form['entity_updates'][$entity_type_id]['#theme']);
    $this->assertEquals($entity_type_label, $form['entity_updates'][$entity_type_id]['#title']);
    $this->assertEquals($field_storage_label ?: $field_storage_config_id, $form['entity_updates'][$entity_type_id]['#items'][$field_storage_config_id]);
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on a non-entity form.
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForNonEntityForm(): void {
    $form = [];

    // Prepare form object.
    /** @var \Drupal\Core\Form\FormInterface|\PHPUnit\Framework\MockObject\MockObject $form_object */
    $form_object = $this->createMock(FormInterface::class);

    // Prepare form state.
    /** @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject $form_state */
    $form_state = $this->createMock(FormStateInterface::class);

    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for for potential field storage configuration updates may only be applied to entity forms.');
    $this->entityViewModeDeleteFormHelper->alter($form, $form_state);
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on a basic 'Entity'
   * entity delete confirm form.
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForWrongEntityType(): void {
    $form = [];

    // Prepare form object.
    /** @var \Drupal\Core\Entity\EntityFormInterface|\PHPUnit\Framework\MockObject\MockObject $form_object */
    $form_object = $this->createMock(EntityFormInterface::class);

    $form_object->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->createMock(EntityInterface::class));

    // Prepare form state.
    /** @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject $form_state */
    $form_state = $this->createMock(FormStateInterface::class);

    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for potential field storage configuration updates may only be applied to view mode entity forms.');
    $this->entityViewModeDeleteFormHelper->alter($form, $form_state);
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on an 'Entity view mode'
   * entity edit form.
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForWrongOperation(): void {
    $form = [];

    // Prepare form object.
    /** @var \Drupal\Core\Entity\EntityFormInterface|\PHPUnit\Framework\MockObject\MockObject $form_object */
    $form_object = $this->createMock(EntityFormInterface::class);

    $form_object->expects($this->once())
      ->method('getEntity')
      ->willReturn($this->createMock(EntityViewModeInterface::class));

    $form_object->expects($this->once())
      ->method('getOperation')
      ->willReturn('edit');

    // Prepare form state.
    /** @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject $form_state */
    $form_state = $this->createMock(FormStateInterface::class);

    $form_state->expects($this->once())
      ->method('getFormObject')
      ->willReturn($form_object);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for potential field storage configuration updates may only be applied to view mode entity delete forms.');
    $this->entityViewModeDeleteFormHelper->alter($form, $form_state);
  }

}
