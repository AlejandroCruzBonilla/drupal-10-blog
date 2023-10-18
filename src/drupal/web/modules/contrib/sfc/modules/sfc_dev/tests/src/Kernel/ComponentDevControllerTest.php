<?php

namespace Drupal\Tests\sfc_dev\Kernel;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\sfc_dev\Controller\ComponentDevController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the component dev controller.
 *
 * @coversDefaultClass \Drupal\sfc_dev\Controller\ComponentDevController
 *
 * @group sfc_dev
 */
class ComponentDevControllerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_dev',
    'sfc_test',
    'system',
  ];

  /**
   * Tests the ::library method.
   */
  public function testLibrary() {
    $manager = $this->createMock(PluginManagerInterface::class);
    $manager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn([
        'one' => [
          'id' => 'one',
          'group' => 'Bgroup',
        ],
        'two' => [
          'id' => 'two',
          'group' => 'Agroup',
        ],
        'three' => [
          'id' => 'three',
          'group' => 'Bgroup',
        ],
        'four' => [
          'id' => 'four',
        ],
        'five' => [
          'id' => 'five',
          'group' => 'Zgroup',
        ],
        'six' => [
          'id' => 'six',
          'alt_id' => 'alt_six',
          'group' => 'Zgroup',
        ],
      ]);
    $resolver = $this->createMock(AssetResolverInterface::class);
    $controller = new ComponentDevController($manager, $resolver);
    $this->assertEquals([
      'Agroup' => [
        'two' => [
          'id' => 'two',
          'group' => 'Agroup',
        ],
      ],
      'Bgroup' => [
        'one' => [
          'id' => 'one',
          'group' => 'Bgroup',
        ],
        'three' => [
          'id' => 'three',
          'group' => 'Bgroup',
        ],
      ],
      'Zgroup' => [
        'alt_six' => [
          'id' => 'six',
          'alt_id' => 'alt_six',
          'group' => 'Zgroup',
        ],
        'five' => [
          'id' => 'five',
          'group' => 'Zgroup',
        ],
      ],
      'Other' => [
        'four' => [
          'id' => 'four',
        ],
      ],
    ], $controller->libraryBase()['#context']['grouped_definitions']);
  }

  /**
   * Tests the ::libraryPreview method.
   */
  public function testLibraryPreview() {
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $twig->setCache(FALSE);

    $controller = ComponentDevController::create($this->container);
    $request = new Request();
    $response = $controller->libraryPreview('say_hello', $request);
    $this->assertStringContainsString('Hello !', (string) $response->getCommands()[0]['data']);
  }

  /**
   * Tests the ::shouldWriteAssets method.
   */
  public function testShouldWriteAssets() {
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $twig->setCache(FALSE);

    $controller = ComponentDevController::create($this->container);
    $response = $controller->shouldWriteAssets('say_hello');
    $this->assertTrue(Json::decode($response->getContent()));
  }

  /**
   * Tests the ::viewTemplate method.
   */
  public function testViewTemplate() {
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $twig->setCache(FALSE);

    $controller = ComponentDevController::create($this->container);
    $this->assertNotEmpty($controller->viewTemplate('say_hello'));
  }

  /**
   * Tests the ::viewTemplateTitle method.
   */
  public function testViewTemplateTitle() {
    /** @var \Drupal\Core\Template\TwigEnvironment $twig */
    $twig = \Drupal::service('twig');
    $twig->setCache(FALSE);

    $controller = ComponentDevController::create($this->container);
    $this->assertEquals('Template for say_hello', $controller->viewTemplateTitle('say_hello'));
  }

}
