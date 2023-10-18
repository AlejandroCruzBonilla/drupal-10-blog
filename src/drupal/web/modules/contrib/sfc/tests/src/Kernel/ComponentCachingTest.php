<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Core\Render\RenderContext;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests that component caching works.
 *
 * @group sfc
 */
class ComponentCachingTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'field',
    'user',
    'text',
    'sfc',
    'sfc_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    NodeType::create([
      'type' => 'article',
    ])->save();
    Node::create(['type' => 'article', 'title' => $this->randomString()])->save();
    Node::create(['type' => 'article', 'title' => $this->randomString()])->save();
    Node::create(['type' => 'article', 'title' => $this->randomString()])->save();
    Node::create(['type' => 'article', 'title' => $this->randomString()])->save();
  }

  /**
   * Tests that caching works.
   */
  public function testCache() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% include "sfc--caching" %}',
    ];
    $context = new RenderContext();
    $output = (string) $renderer->executeInRenderContext($context, function () use ($element, $renderer) {
      return $renderer->render($element);
    });
    $this->assertStringContainsString("I'm cached!", $output);
    /** @var \Drupal\Core\Render\BubbleableMetadata $metadata */
    $metadata = $context->pop();
    $expected = [
      'my_tag',
      'node:1',
      'node:2',
      'node:3',
      'node:4',
      'other_tag',
    ];
    $real = $metadata->getCacheTags();
    sort($expected);
    sort($real);
    $this->assertEquals($expected, $real);
    $this->assertContains('url.query_args', $metadata->getCacheContexts());
    $this->assertEquals(123, $metadata->getCacheMaxAge());
  }

}
