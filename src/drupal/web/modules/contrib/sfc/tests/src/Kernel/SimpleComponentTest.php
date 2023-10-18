<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the basic behavior of simple components.
 *
 * @coversDefaultClass \Drupal\sfc\Plugin\SingleFileComponent\SimpleComponent
 *
 * @group sfc
 */
class SimpleComponentTest extends KernelTestBase {

  use ComponentTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
    'system',
  ];

  /**
   * Tests that a simple component renders properly.
   */
  public function testRender() {
    $this->assertEquals('<div class="simple-test">Default value</div>', $this->renderComponent('simple_test', []));
    $this->assertEquals('<div class="simple-test">Click me</div>', $this->renderComponent('simple_test', [
      'message' => 'Click me',
    ]));
    // Test the output CSS/JS.
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('simple_assets');
    $this->assertTrue($component->shouldWriteAssets());
    $component->writeAssets();
    $this->assertContains('foo/bar', $component->getLibrary()['dependencies']);
    $this->assertFileExists('public://sfc/components/simple_assets/simple_assets.css');
    $this->assertFileExists('public://sfc/components/simple_assets/simple_assets.js');
    $css = file_get_contents('public://sfc/components/simple_assets/simple_assets.css');
    $this->assertStringContainsString('.foo {', $css);
    $this->assertStringContainsString('background: url(/' . \Drupal::service('extension.list.module')->getPath('sfc_test') . '/assets/image.jpg)', $css);
    $js = file_get_contents('public://sfc/components/simple_assets/simple_assets.js');
    $this->assertStringContainsString("alert('foo');", $js);
    $this->assertStringContainsString("alert('bar');", $js);
    $this->assertStringContainsString("alert('baz');", $js);
    // Test an empty template.
    $this->assertEquals('', $this->renderComponent('simple_empty', []));
    // Test a hard to parse file.
    $component = $manager->createInstance('simple_parse');
    $component->writeAssets();
    $this->assertFileExists('public://sfc/components/simple_parse/simple_parse.js');
    $js = file_get_contents('public://sfc/components/simple_parse/simple_parse.js');
    $expected_js = "
  var foo = '<script>alert(`bar`)</script>';
  var template = '<template>baz</template>';

(function ($, Drupal, drupalSettings) {";
    $this->assertEquals($expected_js, substr($js, 0, strlen($expected_js)));
    $this->assertEquals("  <template>foo</template>
  bar
  <script>alert(`baz`)</script>", $this->renderComponent('simple_parse', []));
    // Test nested unique IDs.
    $this->assertStringContainsString('simple_unique_id', $this->renderComponent('simple_nested_unique_id', []));
  }

  /**
   * Tests that form methods work as expected.
   */
  public function testForm() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('simple_block');
    $form = [];
    $form_state = new FormState();
    $form = $component->buildContextForm($form, $form_state, []);
    $this->assertNotEmpty($form);
    $form['message']['#parents'] = [];
    $form_state->setValue('message', 'test');
    $component->validateContextForm($form, $form_state);
    $this->assertNotEmpty($form_state->hasAnyErrors());
    $form_state->setValue('message', 'changeme');
    $component->submitContextForm($form, $form_state);
    $this->assertEquals('changed', $form_state->getValue('message'));
  }

  /**
   * Tests that definition additions work as expected.
   */
  public function testDefinitionAdditions() {
    /** @var \Drupal\sfc\Plugin\Block\ComponentBlock $block */
    $block = \Drupal::service('plugin.manager.block')->createInstance('single_file_component_block:simple_block');
    $renderer = \Drupal::service('renderer');
    $build = $block->build();
    $render = $renderer->renderPlain($build);
    $this->assertEquals('<div class="simple-block">Default value</div>', $render);
  }

  /**
   * Tests that parsing complex templates works as expected.
   */
  public function testComplexTemplate() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('simple_complex_template');
    $this->assertStringContainsString("<div{{ attributes.addClass('two-column') }}>
    <div{{ region_attributes.left.addClass('left') }}>
      {{ content.left }}
    </div>
    <div{{ region_attributes.right.addClass('right') }}>
      {{ content.right }}
    </div>
  </div>", $component->getTemplate());
  }

  /**
   * Tests the interaction between components and themes.
   */
  public function testThemeComponents() {
    // Themes can provide components.
    \Drupal::service('theme_installer')->install(['sfc_test_theme']);
    /** @var \Drupal\sfc\ComponentPluginManager $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('theme_component');
    $this->assertStringContainsString("I'm from a theme!", $component->getTemplate());

    // Sub-themes can override parent-theme components.
    \Drupal::service('theme_installer')->install(['sfc_sub_theme']);
    \Drupal::configFactory()
      ->getEditable('system.theme')
      ->set('default', 'sfc_sub_theme')
      ->save();
    $manager->clearCachedDefinitions();
    $component = $manager->createInstance('theme_component');
    $this->assertStringContainsString("I'm from a sub-theme!", $component->getTemplate());

    // Themes can override Twig templates.
    $this->assertEquals('I took your template!', $this->renderComponent('simple_test', []));
  }

  /**
   * Tests that a simple component with vanilla JS works as expected.
   */
  public function testVanillaJs() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('simple_vanilla_js');
    $this->assertTrue($component->shouldWriteAssets());
    $component->writeAssets();
    $this->assertFileExists('public://sfc/components/simple_vanilla_js/simple_vanilla_js.js');
    $js = file_get_contents('public://sfc/components/simple_vanilla_js/simple_vanilla_js.js');
    $this->assertStringContainsString("alert('foo');", $js);
    $this->assertStringContainsString("once('sfcAttach'", $js);
    $this->assertStringNotContainsString("jQuery", $js);
  }

  /**
   * Tests that actions work as expected.
   */
  public function testActions() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    /** @var \Drupal\sfc\Plugin\SingleFileComponent\SimpleComponent $component */
    $component = $manager->createInstance('simple_actions');
    $request = new Request(['hello' => 'world']);
    $result = $component->performAction('return_query', $request);
    $this->assertEquals('world', $result);
    // Test that autowiring works.
    $result = $component->performAction('user_id', $request);
    $this->assertEquals(\Drupal::currentUser()->id(), $result);
  }

  /**
   * Tests that local assets work as expected.
   */
  public function testLocalLibrary() {
    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.single_file_component');
    $component = $manager->createInstance('simple_local_assets');
    $this->stringContains('main.css', array_keys($component->getLibrary()['css']['base'])[0]);
    $this->stringContains('simple_local_assets.css', array_keys($component->getLibrary()['css']['base'])[1]);
    $this->assertTrue(file_exists($this->root . array_keys($component->getLibrary()['css']['base'])[1]));
    $this->assertTrue(file_exists($this->root . array_keys($component->getLibrary()['js'])[0]));
    $this->assertEquals(['core/sortable'], $component->getLibrary()['dependencies']);
    $component = $manager->createInstance('simple_local_deps');
    $this->assertEquals(['core/jquery', 'core/drupal.ajax', 'core/drupal'], $component->getLibrary()['dependencies']);
  }

}
