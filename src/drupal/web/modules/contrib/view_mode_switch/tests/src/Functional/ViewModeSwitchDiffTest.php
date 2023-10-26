<?php

namespace Drupal\Tests\view_mode_switch\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\view_mode_switch\Traits\ViewModeSwitchTestTrait;

/**
 * Tests the view mode switch diff plugin.
 *
 * @group view_mode_switch
 *
 * @requires module diff
 */
class ViewModeSwitchDiffTest extends ViewModeSwitchTestBase {

  use ViewModeSwitchTestTrait;

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = [
    'diff',
    'field',
    'node',
  ];

  /**
   * An administrator test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A test view mode switch field.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $fieldVms;

  /**
   * A test node type.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  protected $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create content type.
    $this->nodeType = $this->drupalCreateContentType([
      'type' => 'vms_test',
      'name' => 'vms_test',
    ]);

    // Create view modes for preview view mode switch testing.
    $this->viewModeFoo = $this->createViewMode('foo', 'node');
    $this->viewModeBar = $this->createViewMode('bar', 'node');

    $this->assertIsString($this->viewModeFoo->id());
    $view_mode_name_foo = $this->getViewModeNameFromId($this->viewModeFoo->id());

    $this->assertIsString($this->viewModeBar->id());
    $view_mode_name_bar = $this->getViewModeNameFromId($this->viewModeBar->id());

    // Create view mode switch field for content type.
    $field_vms_origin_view_modes = ['default'];
    $field_vms_allowed_view_modes = [
      $view_mode_name_foo,
      $view_mode_name_bar,
    ];
    $this->assertIsString($this->nodeType->id());
    $this->fieldVms = $this->createViewModeSwitchField('vms', $field_vms_origin_view_modes, $field_vms_allowed_view_modes, FALSE, 'node', $this->nodeType->id());

    // Disable visual inline diff.
    $config = $this->config('diff.settings')
      ->set('general_settings.layout_plugins.visual_inline.enabled', FALSE);
    $config->save();

    // Create and log in administrator user.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'administer nodes',
      'view all revisions',
    ]);

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests for diff plugin of View Mode Switch Field.
   *
   * Tests that a diff is displayed when changes are made in a view mode switch
   * field.
   */
  public function testViewModeSwitchDiff(): void {
    $page = $this->getSession()->getPage();
    $this->assertTrue(TRUE);

    // Ensure view mode switch field diff plugin is set.
    $this->drupalGet('admin/config/content/diff/fields');
    $page->selectFieldOption('fields[node__' . $this->fieldVms->getName() . '][plugin][type]', 'view_mode_switch_field_diff_builder');
    $page->pressButton('Save');

    $this->assertIsString($this->viewModeFoo->id());
    $view_mode_name_foo = $this->getViewModeNameFromId($this->viewModeFoo->id());

    $this->assertIsString($this->viewModeBar->id());
    $view_mode_name_bar = $this->getViewModeNameFromId($this->viewModeBar->id());

    // Create test node.
    $node = Node::create([
      'title' => 'VMS test',
      'type' => $this->nodeType->id(),
      $this->fieldVms->getName() => $view_mode_name_foo,
    ]);
    $this->assertInstanceOf(NodeInterface::class, $node);
    $node->save();

    // Switch view mode on test node and create new revision.
    $this->assertIsString($this->viewModeBar->id());
    $node->set($this->fieldVms->getName(), $view_mode_name_bar);
    $node->setNewRevision();
    $node->save();

    // Compare the revisions of the test node.
    $this->drupalGet('node/' . $node->id() . '/revisions');
    $page->pressButton('Compare selected revisions');

    // Assert the view mode switch field changes.
    $element_deleted = $page->find('css', '.diff-context.diff-deletedline:not(.diff-marker):not(.diff-line-number)');
    $this->assertInstanceOf(NodeElement::class, $element_deleted);
    $element_added = $page->find('css', '.diff-context.diff-addedline:not(.diff-marker):not(.diff-line-number)');
    $this->assertInstanceOf(NodeElement::class, $element_added);
    $this->assertEquals($view_mode_name_foo, $element_deleted->getText());
    $this->assertEquals($view_mode_name_bar, $element_added->getText());
  }

}
