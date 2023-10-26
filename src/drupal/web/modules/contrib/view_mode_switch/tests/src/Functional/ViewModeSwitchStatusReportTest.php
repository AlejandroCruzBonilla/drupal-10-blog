<?php

namespace Drupal\Tests\view_mode_switch\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\user\UserInterface;

/**
 * Tests view mode switch status report functionality.
 *
 * @group view_mode_switch
 */
class ViewModeSwitchStatusReportTest extends ViewModeSwitchTestBase {

  use StringTranslationTrait;

  /**
   * An administrator test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A test user with access to status report.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $reportUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $report_user = $this->createUser([
      'administer site configuration',
    ]);
    $this->assertInstanceOf(UserInterface::class, $report_user);
    $this->reportUser = $report_user;

    $admin_user = $this->createUser([
      'administer entity_test fields',
      'administer site configuration',
    ]);
    $this->assertInstanceOf(UserInterface::class, $admin_user);
    $this->adminUser = $admin_user;
  }

  /**
   * Tests view mode switch field status report requirement values.
   */
  public function testRequirementValues(): void {
    $this->drupalLogin($this->reportUser);

    $assert_session = $this->assertSession();

    $expected_summary = $this->t('View mode switch');

    // Test status report without any view mode switch fields created.
    $this->drupalGet('admin/reports/status');
    $assert_session->elementNotExists('css', 'details.system-status-report__entry summary:contains("' . $expected_summary . '")');

    // Test status report with valid view mode switch field created.
    $this->createViewModeSwitchField('valid', ['full'], ['teaser']);
    $this->drupalGet('admin/reports/status');
    $assert_session->elementExists('css', 'details.system-status-report__entry summary:contains("' . $expected_summary . '") + div:contains("' . $this->t('All fields configured properly') . '")');

    // Test status report with one invalid view mode switch field created.
    $field_invalid1 = $this->createViewModeSwitchField('invalid1', [], ['teaser']);
    $field_storage_invalid1 = $field_invalid1->getFieldStorageDefinition();
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_invalid1);
    $this->assertIsString($field_storage_invalid1->id());
    $this->drupalGet('admin/reports/status');
    $requirement_value = $this->cssSelect('details.system-status-report__entry summary:contains("' . $expected_summary . '") + div');
    $this->assertStringContainsString((string) $this->t('1 invalid field storage configuration detected'), $requirement_value[0]->getText());
    $this->assertStringContainsString((string) $this->t('The following field storage is missing an origin view mode configuration:'), $requirement_value[0]->getText());
    $this->assertStringContainsString($field_storage_invalid1->id(), $requirement_value[0]->getText());
    // Test usages output without links.
    $usages = $requirement_value[0]->findAll('css', '.item-list__comma-list li:contains("' . $this->t('Entity Test Bundle') . '")');
    $this->assertEquals(1, count($usages));
    $usages_links = $requirement_value[0]->findAll('css', '.item-list__comma-list li > a');
    $this->assertEquals(0, count($usages_links));

    // Test status report with another invalid view mode switch field created.
    $field_invalid2 = $this->createViewModeSwitchField('invalid2', [], ['teaser']);
    $field_storage_invalid2 = $field_invalid2->getFieldStorageDefinition();
    $this->assertInstanceOf(FieldStorageConfigInterface::class, $field_storage_invalid2);
    $this->assertIsString($field_storage_invalid2->id());
    $this->drupalGet('admin/reports/status');
    $requirement_value = $this->cssSelect('details.system-status-report__entry summary:contains("' . $expected_summary . '") + div');
    $this->assertStringContainsString('2 invalid field storage configurations detected', $requirement_value[0]->getText());
    $this->assertStringContainsString('The following field storages are missing an origin view mode configuration:', $requirement_value[0]->getText());
    $this->assertStringContainsString($field_storage_invalid1->id(), $requirement_value[0]->getText());
    $this->assertStringContainsString($field_storage_invalid2->id(), $requirement_value[0]->getText());
    // Test usages output without links.
    $usages = $requirement_value[0]->findAll('css', '.item-list__comma-list li:contains("' . $this->t('Entity Test Bundle') . '")');
    $this->assertEquals(2, count($usages));
    $usages_links = $requirement_value[0]->findAll('css', '.item-list__comma-list li > a');
    $this->assertEquals(0, count($usages_links));

    // Test status report as administrator user.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/reports/status');
    $requirement_value = $this->cssSelect('details.system-status-report__entry summary:contains("' . $expected_summary . '") + div');
    // Test linked usages output.
    $usages = $requirement_value[0]->findAll('css', '.item-list__comma-list li > a[href$="/entity_test/structure/entity_test/fields"]:contains("' . $this->t('Entity Test Bundle') . '")');
    $this->assertEquals(2, count($usages));
  }

}
