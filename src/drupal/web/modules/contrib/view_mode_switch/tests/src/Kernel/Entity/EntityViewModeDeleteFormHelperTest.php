<?php

namespace Drupal\Tests\view_mode_switch\Kernel\Entity;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\system\Form\RssFeedsForm;
use Drupal\Tests\view_mode_switch\Kernel\ViewModeSwitchTestBase;

/**
 * Tests the view mode switch entity view mode delete form helper.
 *
 * @group view_mode_switch
 * @coversDefaultClass \Drupal\view_mode_switch\Entity\EntityViewModeDeleteFormHelper
 */
class EntityViewModeDeleteFormHelperTest extends ViewModeSwitchTestBase {

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'view_mode_switch_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityFormBuilder = $this->container->get('entity.form_builder');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->formBuilder = $this->container->get('form_builder');
  }

  /**
   * Tests field storage updates appear in view mode delete form (if necessary).
   *
   * @covers ::alter
   */
  public function testAlter(): void {
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage_foo */
    $field_storage_foo = $this->fieldFoo->getFieldStorageDefinition();
    $entity_type_id = $field_storage_foo->getEntityTypeId();
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $this->assertInstanceOf(EntityTypeInterface::class, $entity_type);
    $entity_type_label = $entity_type->getLabel();

    // Test delete confirm form of view mode that is used as origin view mode.
    $form = $this->entityFormBuilder->getForm($this->viewModeFoo, 'delete');
    $this->assertSame($form['entity_updates']['field_storage_config']['#items'], [$field_storage_foo->id() => $field_storage_foo->id()]);
    $this->assertEquals($entity_type_label, $form['entity_updates']['field_storage_config']['#title']);

    // Test delete confirm form of view mode that is not used as origin view
    // mode.
    $form = $this->entityFormBuilder->getForm($this->viewModeFoo1, 'delete');
    $this->assertTrue(!isset($form['entity_updates']['field_storage_config']['#items'][$field_storage_foo->id()]));
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on an 'Entity test'
   * entity delete confirm form.
   *
   * @see \view_mode_switch_test_form_entity_test_entity_test_delete_form_form_alter()
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForWrongEntityType(): void {
    // Test exception when form alter is applied to 'delete' operation entity
    // form for wrong entity type.
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for potential field storage configuration updates may only be applied to view mode entity forms.');
    $this->entityFormBuilder->getForm(EntityTest::create(['view_mode_switch_form_alter_test' => TRUE]), 'delete');
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on an 'Entity view mode'
   * entity edit form.
   *
   * @see \view_mode_switch_test_form_entity_view_mode_edit_form_alter()
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForWrongOperation(): void {
    // Test exception when form alter is applied to wrong operation entity form.
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for potential field storage configuration updates may only be applied to view mode entity delete forms.');
    $this->entityFormBuilder->getForm(EntityViewMode::create([
      'targetEntityType' => 'entity_test',
      'id' => 'entity_test.test_wrong_operation',
      'view_mode_switch_form_alter_test' => TRUE,
    ]), 'edit');
  }

  /**
   * Tests exception thrown by view mode delete form alter in wrong context.
   *
   * In this test the form alter is tried to be applied on a non-entity form.
   *
   * @see \view_mode_switch_test_form_system_rss_feeds_settings_alter()
   *
   * @covers ::alter
   */
  public function testAlterExceptionsForNonEntityForm(): void {
    // Test exception when form alter is applied to wrong form.
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Required form alters for for potential field storage configuration updates may only be applied to entity forms.');
    // @phpstan-ignore-next-line
    $this->formBuilder->getForm(RssFeedsForm::class, ['view_mode_switch_form_alter_test' => TRUE]);
  }

}
