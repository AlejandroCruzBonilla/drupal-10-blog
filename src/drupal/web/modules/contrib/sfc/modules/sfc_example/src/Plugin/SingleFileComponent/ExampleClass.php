<?php

namespace Drupal\sfc_example\Plugin\SingleFileComponent;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sfc\ComponentBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains an example single file component in a class.
 *
 * Class components are great for using dependency injection and unit testing,
 * or if you prefer traditional PHP to the inline PHP in .sfc files.
 *
 * @SingleFileComponent(
 *   id = "example_class",
 *   group = "Example",
 * )
 */
class ExampleClass extends ComponentBase {

  const TEMPLATE = <<<TWIG
<p class="example-class">Hello {{ name }}!</p>
TWIG;

  const CSS = <<<CSS
.example-class {
  color: darkred;
}
CSS;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a ExampleClass object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param bool $debug
   *   If debug markup should be added to templates.
   * @param string $app_root
   *   The app root.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $debug, $app_root, FileSystemInterface $file_system, FileUrlGeneratorInterface $file_url_generator, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $debug, $app_root, $file_system, $file_url_generator);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      static::getDebugParameter($container),
      $container->getParameter('app.root'),
      $container->get('file_system'),
      $container->get('file_url_generator'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (!isset($context['name'])) {
      $context['name'] = $this->currentUser->getDisplayName();
    }
  }

}
