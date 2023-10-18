<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests methods provided by the component block.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\Field\FieldFormatter\ComponentFieldFormatter
 *
 * @group sfc
 */
class ComponentFieldFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
    'node',
    'field',
    'system',
    'user',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $type = NodeType::create(['type' => 'page']);
    $type->save();
    FieldStorageConfig::create([
      'field_name' => 'multiple_text',
      'type' => 'text',
      'entity_type' => 'node',
      'cardinality' => -1,
    ])->save();
    FieldConfig::create([
      'field_name' => 'multiple_text',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'multiple_text',
    ])->save();
  }

  /**
   * Tests the ::viewElements method.
   */
  public function testViewElements() {
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test',
      'multiple_text' => [
        'first',
        'second',
      ],
    ]);
    /** @var \Drupal\Core\Field\FormatterBase $formatter */
    $formatter = \Drupal::service('plugin.manager.field.formatter')->createInstance('single_file_component_field_formatter:bold_formatter', [
      'field_definition' => $node->get('title')->getFieldDefinition(),
      'settings' => [],
      'label' => '',
      'view_mode' => '',
      'third_party_settings' => [],
    ]);
    $renderer = \Drupal::service('renderer');
    $build = $formatter->viewElements($node->get('title'), 'en');
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="bold-formatter">Test</div>', $render);
    $formatter->setSetting('component_context', [
      'text' => 'foo',
    ]);
    $build = $formatter->viewElements($node->get('title'), 'en');
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="bold-formatter">foo</div>', $render);
    $formatter->setSetting('component_context', []);
    $build = $formatter->viewElements($node->get('title'), 'en');
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="bold-formatter">Test</div>', $render);
    // Test multiple.
    /** @var \Drupal\Core\Field\FormatterBase $formatter */
    $formatter = \Drupal::service('plugin.manager.field.formatter')->createInstance('single_file_component_field_formatter:multiple_formatter', [
      'field_definition' => $node->get('multiple_text')->getFieldDefinition(),
      'settings' => [],
      'label' => '',
      'view_mode' => '',
      'third_party_settings' => [],
    ]);
    $renderer = \Drupal::service('renderer');
    $build = $formatter->viewElements($node->get('multiple_text'), 'en');
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<ul class="multiple-formatter">
  <li>first</li>
  <li>second</li>
</ul>', $render);
  }

  /**
   * Tests the ::settingsForm method.
   */
  public function testSettingsForm() {
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test',
    ]);
    /** @var \Drupal\Core\Field\FormatterBase $formatter */
    $formatter = \Drupal::service('plugin.manager.field.formatter')->createInstance('single_file_component_field_formatter:bold_formatter', [
      'field_definition' => $node->get('title')->getFieldDefinition(),
      'settings' => [],
      'label' => '',
      'view_mode' => '',
      'third_party_settings' => [],
    ]);
    $form = $formatter->settingsForm([], new FormState());
    $this->assertNotEmpty($form['component_context']);
  }

}
