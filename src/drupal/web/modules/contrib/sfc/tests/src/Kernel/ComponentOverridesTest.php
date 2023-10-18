<?php

namespace Drupal\Tests\sfc\Kernel;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests that component overrides work.
 *
 * @group sfc
 */
class ComponentOverridesTest extends KernelTestBase {

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
      'type' => 'page',
    ])->save();
    NodeType::create([
      'type' => 'article',
    ])->save();

    DateFormat::create([
      'id' => 'fallback',
      'pattern' => 'F d, Y',
    ])->save();
  }

  /**
   * Tests that overrides work.
   */
  public function testOverrides() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $builder = \Drupal::entityTypeManager()->getViewBuilder('node');

    $article = Node::create([
      'title' => 'article',
      'type' => 'article',
    ]);
    $article->save();
    $page = Node::create([
      'title' => 'page',
      'type' => 'page',
    ]);
    $page->save();

    $build = $builder->view($article);
    $this->assertStringContainsString('Hello <span>article</span>', str_replace("\n", "", (string) $renderer->renderPlain($build)));

    $build = $builder->view($page);
    $this->assertStringNotContainsString('Hello <span>page</span>', str_replace("\n", "", (string) $renderer->renderPlain($build)));
  }

}
