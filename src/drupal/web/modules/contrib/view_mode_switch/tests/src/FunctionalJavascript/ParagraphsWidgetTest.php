<?php

namespace Drupal\Tests\view_mode_switch\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\Component\Utility\Html;
use Drupal\Tests\paragraphs\FunctionalJavascript\ParagraphsTestBaseTrait;
use Drupal\Tests\view_mode_switch\Traits\ViewModeSwitchTestTrait;

/**
 * Tests the paragraphs experimental widget preview when switching view modes.
 *
 * @group view_mode_switch
 *
 * @requires module paragraphs
 */
class ParagraphsWidgetTest extends ViewModeSwitchTestBase {

  use ParagraphsTestBaseTrait;
  use ViewModeSwitchTestTrait;

  /**
   * An administrator test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'field_ui',
    'node',
    'paragraphs',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
    ]);

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test paragraphs experimental widget's preview when switching its view mode.
   */
  public function testParagraphsWidgetPreview(): void {
    $page = $this->getSession()->getPage();

    /** @var \Drupal\FunctionalJavascriptTests\JSWebAssert $session */
    $session = $this->assertSession();

    $node_bundle = 'test_bundle';
    $node_field_paragraphs_name = 'field_paragraphs';

    $paragraph_bundle = 'text';
    $paragraph_field_text_name = 'field_text';
    $paragraph_field_text_value = 'Foo';
    $paragraph_view_mode_preview_test = 'preview_test';

    // Create paragraphed content type with paragraphs field.
    $this->addParagraphedContentType($node_bundle, $node_field_paragraphs_name);

    // Create paragraph type with text field.
    $this->addParagraphsType($paragraph_bundle);
    $this->addFieldtoParagraphType($paragraph_bundle, $paragraph_field_text_name, 'text');

    // Set paragraph widget settings.
    $this->setParagraphsWidgetSettings($node_bundle, $node_field_paragraphs_name, [
      'add_mode' => 'button',
      'closed_mode' => 'preview',
      'default_paragraph_type' => 'text',
    ]);

    // Create paragraphs view mode for preview view mode switch testing.
    $this->createViewMode($paragraph_view_mode_preview_test, 'paragraph');

    // Create view mode switch field for paragraph type.
    $paragraph_field_vms_origin_view_modes = ['preview'];
    $paragraph_field_vms_allowed_view_modes = [$paragraph_view_mode_preview_test];
    $paragraph_field_vms = $this->createViewModeSwitchField('vms', $paragraph_field_vms_origin_view_modes, $paragraph_field_vms_allowed_view_modes, FALSE, 'paragraph', $paragraph_bundle);

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = $this->container->get('entity_display.repository');

    // Add view mode switch field to paragraph type's default entity form
    // display.
    $entity_display_repository->getFormDisplay('paragraph', $paragraph_bundle)
      ->setComponent($paragraph_field_vms->getName(), ['type' => 'view_mode_switch'])
      ->save();

    // Add view mode switch field to 'text' paragraph's 'preview_test' display.
    $entity_display_repository->getViewDisplay('paragraph', $paragraph_bundle, $paragraph_view_mode_preview_test)
      ->setComponent($paragraph_field_vms->getName(), [
        'type' => 'view_mode_switch_default',
        'region' => 'content',
      ])
      ->save();

    // Go to node create form and test paragraphs widget's preview output when
    // switching view modes.
    $this->drupalGet('node/add/' . $node_bundle);

    // Fill paragraph's text field.
    $paragraph_field_text_locator = $node_field_paragraphs_name . '[0][subform][' . $paragraph_field_text_name . '][0][value]';
    $page->fillField($paragraph_field_text_locator, $paragraph_field_text_value);

    // Switch to paragraph's preview.
    $this->pressParagraphsWidgetCollapseButton($node_field_paragraphs_name);
    $this->assertParagraphsWidgetPreviewContains($paragraph_field_text_value, $node_field_paragraphs_name);
    $this->assertParagraphsWidgetPreviewNotContains($paragraph_view_mode_preview_test, $node_field_paragraphs_name);

    // Switch back to paragraph's edit form and change view mode.
    $this->pressParagraphsWidgetEditButton($node_field_paragraphs_name);
    $paragraph_field_vms_locator = $node_field_paragraphs_name . '[0][subform][' . $paragraph_field_vms->getName() . '][0][value]';
    $paragraph_field_vms_locator_css = '[name="' . $paragraph_field_vms_locator . '"]';
    $session->waitForElement('css', $paragraph_field_vms_locator_css);
    $page->selectFieldOption($paragraph_field_vms_locator, $paragraph_view_mode_preview_test);

    // Switch to paragraph's preview.
    $this->pressParagraphsWidgetCollapseButton($node_field_paragraphs_name);
    $this->assertParagraphsWidgetPreviewContains($paragraph_view_mode_preview_test, $node_field_paragraphs_name);
    $this->assertParagraphsWidgetPreviewNotContains($paragraph_field_text_value, $node_field_paragraphs_name);
  }

  /**
   * Evaluates that a paragraph widget's preview contains certain text.
   *
   * @param string $text
   *   The text to search for.
   * @param string $field_name
   *   The name of the paragraphs field to check.
   * @param int $delta
   *   The delta of the paragraph's item to check.
   */
  protected function assertParagraphsWidgetPreviewContains($text, $field_name, $delta = 0): void {
    $this->assertStringContainsString($text, $this->getParagraphsWidgetPreviewText($field_name, $delta));
  }

  /**
   * Evaluates that a paragraph widget's preview does not contain certain text.
   *
   * @param string $text
   *   The text to search for.
   * @param string $field_name
   *   The name of the paragraphs field to check.
   * @param int $delta
   *   The delta of the paragraph's item to check.
   */
  protected function assertParagraphsWidgetPreviewNotContains($text, $field_name, $delta = 0): void {
    $this->assertStringNotContainsString($text, $this->getParagraphsWidgetPreviewText($field_name, $delta));
  }

  /**
   * Returns a paragraphs widget preview's text.
   *
   * @param string $field_name
   *   The name of the paragraphs field to get the preview text for.
   * @param int $delta
   *   The delta of the paragraph's item to get the preview text for.
   *
   * @return string
   *   The paragraphs widget's text.
   */
  protected function getParagraphsWidgetPreviewText($field_name, $delta = 0): string {
    /** @var \Drupal\FunctionalJavascriptTests\JSWebAssert $session */
    $session = $this->assertSession();

    $locator = '[data-drupal-selector="edit-' . Html::cleanCssIdentifier($field_name) . '-' . $delta . '-preview"]';
    $element = $session->waitForElement('css', $locator);
    $this->assertInstanceOf(NodeElement::class, $element);

    return $element->getText();
  }

  /**
   * Presses a paragraphs widget's edit button.
   *
   * @param string $field_name
   *   The name of the paragraphs field to get the edit button for.
   * @param int $delta
   *   The delta of the paragraph's item to get the edit button for.
   */
  protected function pressParagraphsWidgetEditButton($field_name, $delta = 0): void {
    $page = $this->getSession()->getPage();

    $page->pressButton($field_name . '_' . $delta . '_edit');
  }

  /**
   * Presses a paragraphs widget's collapse button.
   *
   * @param string $field_name
   *   The name of the paragraphs field to get the collapse button for.
   * @param int $delta
   *   The delta of the paragraph's item to get the collapse button for.
   */
  protected function pressParagraphsWidgetCollapseButton($field_name, $delta = 0): void {
    $page = $this->getSession()->getPage();

    $page->pressButton($field_name . '_' . $delta . '_collapse');
  }

}
