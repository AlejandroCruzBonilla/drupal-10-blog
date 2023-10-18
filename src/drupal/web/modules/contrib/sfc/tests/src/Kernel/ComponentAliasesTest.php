<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that component aliases work.
 *
 * @group sfc
 */
class ComponentAliasesTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
  ];

  /**
   * Tests that aliases work.
   */
  public function testAliases() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% include "fancy/aliased" %}',
    ];
    $this->assertEquals('Hello ', $renderer->renderPlain($element));
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% include "aliased" %}',
    ];
    $this->assertEquals('Hello ', $renderer->renderPlain($element));
  }

}
