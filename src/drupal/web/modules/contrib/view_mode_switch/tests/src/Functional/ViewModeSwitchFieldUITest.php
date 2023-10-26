<?php

namespace Drupal\Tests\view_mode_switch\Functional;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\field_ui\Traits\FieldUiTestTrait;

/**
 * Tests view mode switch field UI functionality.
 *
 * @group view_mode_switch
 */
class ViewModeSwitchFieldUITest extends ViewModeSwitchTestBase {

  use FieldUiTestTrait;

  /**
   * An administrator test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The test entity form display.
   *
   * @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   */
  protected $entityFormDisplay;

  /**
   * The test entity view display.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $entityViewDisplay;

  /**
   * A test view mode switch field.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $field;

  /**
   * A required test view mode switch field.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $fieldRequired;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->createViewMode('origin1');
    $this->createViewMode('origin2');
    $this->createViewMode('switch1');
    $this->createViewMode('switch2');
    $this->createViewMode('switch3');
    $this->createViewMode('switch4');

    $field_origin_view_modes = ['origin1', 'test'];
    $field_allowed_view_modes = ['switch1', 'switch2'];
    $this->field = $this->createViewModeSwitchField('vms', $field_origin_view_modes, $field_allowed_view_modes);
    $this->assertIsString($this->field->getTargetEntityTypeId());
    $this->assertIsString($this->field->getTargetBundle());

    $field_required_origin_view_modes = ['origin2'];
    $field_required_allowed_view_modes = ['switch3', 'switch4'];
    $this->fieldRequired = $this->createViewModeSwitchField('vms_required', $field_required_origin_view_modes, $field_required_allowed_view_modes, TRUE);

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = \Drupal::service('entity_display.repository');

    // Create/configure default entity form display.
    $this->entityFormDisplay = $entity_display_repository->getFormDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->field->getName())
      ->setComponent($this->fieldRequired->getName());
    $this->entityFormDisplay->save();

    // Create/configure default entity view display.
    $this->entityViewDisplay = $entity_display_repository->getViewDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->field->getName())
      ->setComponent($this->fieldRequired->getName());
    $this->entityViewDisplay->save();

    $this->adminUser = $this->drupalCreateUser([
      'view test entity',
      'administer entity_test content',
      'administer entity_test display',
      'administer entity_test form display',
      'administer entity_test fields',
    ]);
  }

  /**
   * Tests the view mode switch field settings forms.
   */
  public function testFieldSettingsForms(): void {
    $this->drupalLogin($this->adminUser);

    $assert = $this->assertSession();
    $type_path = 'entity_test/structure/entity_test';

    // Test view mode switch field storage settings form.
    $this->drupalGet($type_path . '/fields/entity_test.entity_test.' . $this->field->getName() . '/storage');
    // Test that field cardinality is not configurable.
    $assert->fieldNotExists('cardinality_number');
    // Test origin view modes.
    $assert->fieldExists('settings[origin_view_modes][default]');
    $assert->checkboxNotChecked('settings[origin_view_modes][default]');
    $assert->fieldExists('settings[origin_view_modes][full]');
    $assert->checkboxNotChecked('settings[origin_view_modes][full]');
    $assert->fieldExists('settings[origin_view_modes][origin1]');
    $assert->checkboxChecked('settings[origin_view_modes][origin1]');
    $assert->fieldExists('settings[origin_view_modes][origin2]');
    $assert->checkboxNotChecked('settings[origin_view_modes][origin2]');
    $assert->fieldExists('settings[origin_view_modes][switch1]');
    $assert->checkboxNotChecked('settings[origin_view_modes][switch1]');
    $assert->fieldExists('settings[origin_view_modes][switch2]');
    $assert->checkboxNotChecked('settings[origin_view_modes][switch2]');
    $assert->fieldExists('settings[origin_view_modes][switch3]');
    $assert->checkboxNotChecked('settings[origin_view_modes][switch3]');
    $assert->fieldExists('settings[origin_view_modes][switch4]');
    $assert->checkboxNotChecked('settings[origin_view_modes][switch4]');
    $assert->fieldExists('settings[origin_view_modes][test]');
    $assert->checkboxChecked('settings[origin_view_modes][test]');

    // Test view mode switch field settings form.
    $this->drupalGet($type_path . '/fields/entity_test.entity_test.' . $this->field->getName());
    // Test allowed view modes.
    $assert->fieldExists('settings[allowed_view_modes][full]');
    $assert->checkboxNotChecked('settings[allowed_view_modes][full]');
    $assert->fieldExists('settings[allowed_view_modes][origin2]');
    $assert->checkboxNotChecked('settings[allowed_view_modes][origin2]');
    $assert->fieldExists('settings[allowed_view_modes][switch1]');
    $assert->checkboxChecked('settings[allowed_view_modes][switch1]');
    $assert->fieldExists('settings[allowed_view_modes][switch2]');
    $assert->checkboxChecked('settings[allowed_view_modes][switch2]');
    $assert->fieldExists('settings[allowed_view_modes][switch3]');
    $assert->checkboxNotChecked('settings[allowed_view_modes][switch3]');
    $assert->fieldExists('settings[allowed_view_modes][switch4]');
    $assert->checkboxNotChecked('settings[allowed_view_modes][switch4]');
    // Test no allowed view mode options exist for 'default' and configured
    // origin view modes.
    $assert->fieldNotExists('settings[allowed_view_modes][default]');
    $assert->fieldNotExists('settings[allowed_view_modes][origin1]');
    $assert->fieldNotExists('settings[allowed_view_modes][test]');
  }

  /**
   * Tests the view mode switch field widget.
   */
  public function testFieldWidget(): void {
    $this->drupalLogin($this->adminUser);
    $assert = $this->assertSession();

    // Field selectors.
    $field_selector = $this->field->getName() . '[0][value]';
    $required_field_selector = $this->fieldRequired->getName() . '[0][value]';

    // Access add form.
    $this->drupalGet('/entity_test/add');

    // Check test field in add form.
    $assert->selectExists($field_selector);
    // Empty option exists with correct label.
    $assert->optionExists($field_selector, $this->t('- No change -'));
    // Check allowed view modes exist.
    $assert->optionExists($field_selector, 'switch1');
    $assert->optionExists($field_selector, 'switch2');
    // Check disallowed view modes do not exist.
    $assert->optionNotExists($field_selector, 'full');
    $assert->optionNotExists($field_selector, 'origin1');
    $assert->optionNotExists($field_selector, 'origin2');
    $assert->optionNotExists($field_selector, 'switch3');
    $assert->optionNotExists($field_selector, 'switch4');
    $assert->optionNotExists($field_selector, 'test');
    // Check field value.
    $assert->fieldValueEquals($field_selector, '');

    // Check required test field in add form.
    $assert->selectExists($required_field_selector);
    // Empty option exists with correct label.
    $assert->optionExists($required_field_selector, $this->t('- Select -'));
    // Check allowed view modes exist.
    $assert->optionExists($required_field_selector, 'switch3');
    $assert->optionExists($required_field_selector, 'switch4');
    // Check disallowed view modes do not exist.
    $assert->optionNotExists($required_field_selector, 'full');
    $assert->optionNotExists($required_field_selector, 'origin1');
    $assert->optionNotExists($required_field_selector, 'origin2');
    $assert->optionNotExists($required_field_selector, 'switch1');
    $assert->optionNotExists($required_field_selector, 'switch2');
    $assert->optionNotExists($required_field_selector, 'test');
    // Check field value.
    $assert->fieldValueEquals($required_field_selector, '');

    // Create test entity.
    $entity = EntityTest::create(['name' => $this->randomString()]);
    $entity->set($this->field->getName(), 'switch1');
    $entity->set($this->fieldRequired->getName(), 'switch3');
    $entity->save();

    // Access edit form.
    $this->drupalGet('/entity_test/manage/' . $entity->id() . '/edit');

    // Check test field in edit form.
    $assert->selectExists($field_selector);
    // Empty option exists with correct label.
    $assert->optionExists($field_selector, $this->t('- No change -'));
    // Check allowed view modes exist.
    $assert->optionExists($field_selector, 'switch1');
    $assert->optionExists($field_selector, 'switch2');
    // Check disallowed view modes do not exist.
    $assert->optionNotExists($field_selector, 'full');
    $assert->optionNotExists($field_selector, 'origin1');
    $assert->optionNotExists($field_selector, 'origin2');
    $assert->optionNotExists($field_selector, 'switch3');
    $assert->optionNotExists($field_selector, 'switch3');
    $assert->optionNotExists($field_selector, 'test');
    // Check field value.
    $assert->fieldValueEquals($field_selector, 'switch1');

    // Check required test field in edit form.
    $assert->selectExists($required_field_selector);
    // Empty option does not exist for required field with value.
    $assert->optionNotExists($required_field_selector, $this->t('- No change -'));
    $assert->optionNotExists($required_field_selector, $this->t('- Select -'));
    // Check allowed view modes exist.
    $assert->optionExists($required_field_selector, 'switch3');
    $assert->optionExists($required_field_selector, 'switch4');
    // Check disallowed view modes do not exist.
    $assert->optionNotExists($required_field_selector, 'full');
    $assert->optionNotExists($required_field_selector, 'origin1');
    $assert->optionNotExists($required_field_selector, 'origin2');
    $assert->optionNotExists($required_field_selector, 'switch1');
    $assert->optionNotExists($required_field_selector, 'switch2');
    $assert->optionNotExists($required_field_selector, 'test');
    $assert->fieldValueEquals($required_field_selector, 'switch3');
  }

  /**
   * Tests the entity form settings for view mode switch fields.
   */
  public function testEntityFormDisplaySettings(): void {
    $this->drupalLogin($this->adminUser);
    $assert = $this->assertSession();

    // Field selectors.
    $widget_type_selector = 'fields[' . $this->field->getName() . '][type]';

    // Access entity form display settings form.
    $this->drupalGet('entity_test/structure/entity_test/form-display');

    // Check field widget type options in form display settings form.
    $assert->optionExists($widget_type_selector, 'view_mode_switch');
    $assert->fieldValueEquals($widget_type_selector, 'view_mode_switch');
  }

  /**
   * Tests the entity display settings for view mode switch fields.
   */
  public function testEntityDisplaySettings(): void {
    $this->drupalLogin($this->adminUser);
    $assert = $this->assertSession();

    // Field selectors.
    $formatter_type_selector = 'fields[' . $this->field->getName() . '][type]';

    // Access entity display settings form.
    $this->drupalGet('entity_test/structure/entity_test/display');

    // Check field formatter type options in display settings form.
    $assert->optionExists($formatter_type_selector, 'view_mode_switch_default');
    $assert->optionExists($formatter_type_selector, 'view_mode_switch_machine_name');
    $assert->fieldValueEquals($formatter_type_selector, 'view_mode_switch_default');
  }

}
