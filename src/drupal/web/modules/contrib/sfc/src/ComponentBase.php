<?php

namespace Drupal\sfc;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base class for single file components.
 *
 * This class allows single file components to provide their CSS, JS, and Twig
 * in a single class.
 */
class ComponentBase extends PluginBase implements ComponentInterface, ContainerFactoryPluginInterface, ComponentFormInterface, ComponentActionsInterface, ComponentFilenameInterface {

  /**
   * A Twig template string.
   */
  const TEMPLATE = '';

  /**
   * An optional CSS string.
   */
  const CSS = NULL;

  /**
   * An optional JS string.
   */
  const JS = NULL;

  /**
   * An optional JS selector, which is required if ATTACH or DETACH is defined.
   */
  const SELECTOR = NULL;

  /**
   * An optional JS string that runs inside a Drupal behavior attachment.
   */
  const ATTACH = NULL;

  /**
   * An optional JS string that runs inside a Drupal behavior detachment.
   */
  const DETACH = NULL;

  /**
   * A boolean indicating if vanilla JS should be used for ATTACH/DETACH.
   */
  const VANILLA_JS = FALSE;

  /**
   * An optional array of library dependencies.
   */
  const DEPENDENCIES = NULL;

  /**
   * An optional library definition.
   */
  const LIBRARY = NULL;

  /**
   * If debug markup should be added to templates.
   *
   * @var bool
   */
  protected $debug;

  /**
   * The app root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a ComponentBase object.
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $debug, $app_root, FileSystemInterface $file_system, FileUrlGeneratorInterface $file_url_generator = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
    $this->debug = $debug;
    $this->appRoot = $app_root;
    if (!$file_url_generator) {
      $file_url_generator = \Drupal::service('file_url_generator');
    }
    $this->fileUrlGenerator = $file_url_generator;
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
      $container->get('file_url_generator')
    );
  }

  /**
   * Gets the debug parameter from the container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return bool
   *   The debug parameter.
   */
  protected static function getDebugParameter(ContainerInterface $container) {
    $debug = FALSE;
    if ($container->hasParameter('twig.config') && is_array($container->getParameter('twig.config'))) {
      $debug = $container->getParameter('twig.config')['debug'];
    }
    return $debug;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return isset($this->pluginDefinition['alt_id']) ? $this->pluginDefinition['alt_id'] : $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    $template = "{# Start - ComponentBase additions #}{{ sfc_prepare_context('" . addcslashes($this->getId(), "'") . "') }}{% if cache %}{{ cache }}{% endif %}" .
    "{{ attach_library('" . addcslashes(ComponentNameHelper::getLibraryName($this), "'") . "') }}{# End - ComponentBase additions #}" .
    $this->getTemplateData();
    if ($this->debug) {
      $this->addDebugInfo($template);
    }
    return $template;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {}

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    $library = [];
    if ($this->hasLibraryData()) {
      $library = $this->getLibraryData();
    }
    if ($this->hasDependencies()) {
      if (!isset($library['dependencies'])) {
        $library['dependencies'] = [];
      }
      $library['dependencies'] = array_values(array_unique(array_merge($library['dependencies'], $this->getDependencies())));
    }
    $directory = $this->getAssetPath();
    $name = $this->getAssetFilename();
    if ($this->hasCss()) {
      $library['css']['theme'][$this->fileUrlGenerator->generateString("$directory/$name.css")] = [];
    }
    if ($this->hasJs() || $this->hasAttachments()) {
      $library['js'][$this->fileUrlGenerator->generateString("$directory/$name.js")] = [];
    }
    if ($this->hasAttachments()) {
      $attachments = $this->getAttachmentData();
      $library['dependencies'][] = 'core/drupal';
      $library['dependencies'][] = 'core/drupalSettings';
      if (isset($attachments['vanilla_js']) && $attachments['vanilla_js']) {
        $library['dependencies'][] = 'core/once';
      }
      else {
        $library['dependencies'][] = 'core/jquery';
        $library['dependencies'][] = 'core/once';
      }
    }
    return $library;
  }

  /**
   * {@inheritdoc}
   */
  public function writeAssets() {
    if ($this->hasAssets()) {
      $directory = $this->getAssetPath();
      $name = $this->getAssetFilename();
      $this->doWriteAssets($directory, $name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function shouldWriteAssets() {
    if ($this->hasAssets()) {
      $directory = $this->getAssetPath();
      $name = $this->getAssetFilename();
      return $this->areAssetsOutdated($directory, $name);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContextForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitContextForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function performAction($name, Request $request) {}

  /**
   * Determines if assets are outdated.
   *
   * @param string $directory
   *   The directory to write assets in.
   * @param string $name
   *   The output filename, without an extension.
   *
   * @return bool
   *   Whether or not assets are outdated.
   */
  protected function areAssetsOutdated($directory, $name) {
    $filename = $this->getComponentFileName();
    if ($this->hasCss()) {
      if (!file_exists("$directory/$name.css")) {
        return TRUE;
      }
      if (filemtime($filename) > filemtime("$directory/$name.css")) {
        return TRUE;
      }
    }
    if ($this->hasJs() || $this->hasAttachments()) {
      if (!file_exists("$directory/$name.js")) {
        return TRUE;
      }
      if (filemtime($filename) > filemtime("$directory/$name.js")) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentFileName() {
    $obj = new \ReflectionClass($this);
    return $obj->getFileName();
  }

  /**
   * Adds debug markup to a template.
   *
   * @param string $template
   *   The template.
   */
  protected function addDebugInfo(&$template) {
    $filename = $this->getComponentFileName();
    $template = implode("\n", [
      '<!-- SFC debug -->',
      "<!-- Component file: $filename -->",
      $template,
      '<!-- End debug -->',
    ]);
  }

  /**
   * Returns the asset path URI or path relative to DRUPAL_ROOT.
   *
   * Note that this must be a writable directory if ::writeAssets() writes
   * data to that directory.
   *
   * @return string
   *   The asset path.
   */
  protected function getAssetPath() {
    $name = preg_replace('/[^A-Za-z0-9_]/', '_', $this->getId());
    return "public://sfc/components/$name";
  }

  /**
   * Gets the asset filename.
   *
   * @return string
   *   The filename, without a trailing extension.
   */
  protected function getAssetFilename() {
    return preg_replace('/[^A-Za-z0-9_]/', '_', $this->getId());
  }

  /**
   * Writes assets to the given directory.
   *
   * @param string $directory
   *   The directory to write assets in.
   * @param string $name
   *   The output filename, without an extension.
   */
  protected function doWriteAssets($directory, $name) {
    if (!is_dir($directory)) {
      $this->fileSystem->mkdir($directory, NULL, TRUE);
    }
    if (!is_writable($directory)) {
      $this->fileSystem->chmod($directory);
    }
    if ($this->hasCss()) {
      file_put_contents("$directory/$name.css", $this->getCss());
    }
    $js = '';
    if ($this->hasJs()) {
      $js .= $this->getJs() . "\n";
    }
    if ($this->hasAttachments()) {
      $attachments = $this->getAttachmentData();
      if (isset($attachments['vanilla_js']) && $attachments['vanilla_js']) {
        $js .= "(function (once, Drupal, drupalSettings) {\n";
        $js .= "  Drupal.behaviors.sfc_{$this->getId()} = {\n";
        if ($attachments['attach']) {
          $js .= "    attach: function attach(context, settings) {\n";
          $js .= "      once('sfcAttach', " . json_encode($attachments['selector']) . ", context).forEach(function (element) {\n";
          $js .= $attachments['attach'] . "\n";
          $js .= "      });\n";
          $js .= "    },\n";
        }
        if ($attachments['detach']) {
          $js .= "    detach: function detach(context, settings, trigger) {\n";
          $js .= "      once('sfcDetach', " . json_encode($attachments['selector']) . ", context).forEach(function (element) {\n";
          $js .= $attachments['detach'] . "\n";
          $js .= "      });\n";
          $js .= "    },\n";
        }
        $js .= "  }\n";
        $js .= "})(once, Drupal, drupalSettings);\n";
      }
      else {
        $js .= "(function ($, Drupal, drupalSettings) {\n";
        $js .= "  Drupal.behaviors.sfc_{$this->getId()} = {\n";
        if ($attachments['attach']) {
          $js .= "    attach: function attach(context, settings) {\n";
          $js .= "      $(once('sfcAttach', $(" . json_encode($attachments['selector']) . ", context).addBack(" . json_encode($attachments['selector']) . "))).each(function () {\n";
          $js .= $attachments['attach'] . "\n";
          $js .= "      });\n";
          $js .= "    },\n";
        }
        $js .= "    detach: function detach(context, settings, trigger) {\n";
        if ($attachments['detach']) {
          $js .= "      $(once('sfcDetach', $(" . json_encode($attachments['selector']) . ", context).addBack(" . json_encode($attachments['selector']) . "))).each(function () {\n";
          $js .= $attachments['detach'] . "\n";
          $js .= "      });\n";
        }
        $js .= "      var element = $(" . json_encode($attachments['selector']) . ", context).addBack(" . json_encode($attachments['selector']) . ");once.remove('sfcAttach', element);once.remove('sfcDetach', element);\n";
        $js .= "    },\n";
        $js .= "  }\n";
        $js .= "})(jQuery, Drupal, drupalSettings);\n";
      }
    }
    if ($js) {
      file_put_contents("$directory/$name.js", $js);
    }
  }

  /**
   * Gets attachment data for this component.
   *
   * @return array
   *   An associative array in the format:
   *   - selector: A JS selector, or NULL if none is defined.
   *   - attach: An JS string, or NULL if none is defined.
   *   - detach: A JS string, or NULL if none is defined.
   */
  protected function getAttachmentData() {
    return [
      'selector' => $this::SELECTOR ? $this::SELECTOR : $this->getFallBackSelector(),
      'attach' => $this::ATTACH,
      'detach' => $this::DETACH,
      'vanilla_js' => $this::VANILLA_JS,
    ];
  }

  /**
   * Gets the fallback selector for use with attachments.
   *
   * @return string
   *   The fallback selector.
   */
  protected function getFallBackSelector() {
    return "[data-sfc-id=\"{$this->getId()}\"]";
  }

  /**
   * Gets CSS for this component.
   *
   * @return string|null
   *   The CSS for this component, or NULL if none is defined.
   */
  protected function getCss() {
    return $this->replaceCssPaths($this::CSS);
  }

  /**
   * Prefixes all relative CSS url() paths with the directory of the module.
   *
   * @param string $css
   *   A CSS string.
   *
   * @return string
   *   The CSS string with replaced paths.
   */
  protected function replaceCssPaths($css) {
    $directory = $this->getProjectPath();
    return preg_replace_callback('/url\(\s*[\'"]?(?!(?:data)+:)([^\'")]+)[\'"]?\s*\)/i', function ($matches) use ($directory) {
      if ($this->isRelativeFile($matches[1])) {
        return str_replace($matches[1], $directory . '/' . $matches[1], $matches[0]);
      }
      return $matches[0];
    }, $css);
  }

  /**
   * Gets JS for this component.
   *
   * @return string|null
   *   The JS for this component, or NULL if none is defined.
   */
  protected function getJs() {
    return $this::JS;
  }

  /**
   * Gets template data for this component.
   *
   * @return string|null
   *   The template for this component, or NULL if none is defined.
   */
  protected function getTemplateData() {
    return $this::TEMPLATE;
  }

  /**
   * Gets library data for this component.
   *
   * @return array|null
   *   The library for this component, or NULL if none is defined.
   */
  protected function getLibraryData() {
    $library = $this::LIBRARY;
    if (isset($library['css'])) {
      foreach ($library['css'] as &$files) {
        $this->processLibraryFiles($files);
      }
    }
    if (isset($library['js'])) {
      $this->processLibraryFiles($library['js']);
    }
    return $library;
  }

  /**
   * Processes library files to make paths relative to the component root.
   *
   * @param array $files
   *   An array of library file definitions, keyed by filename.
   */
  protected function processLibraryFiles(array &$files) {
    $directory = $this->getProjectPath();
    foreach ($files as $filename => $info) {
      if ($this->isRelativeFile($filename)) {
        $files["$directory/$filename"] = $info;
        unset($files[$filename]);
      }
    }
  }

  /**
   * Returns the path to the project that provides this file.
   *
   * @return string
   *   The module path.
   */
  protected function getProjectPath() {
    $provider = $this->pluginDefinition['provider'];
    $absolute_path = preg_replace("/$provider\/.*/", $provider, $this->getComponentFileName());
    return str_replace($this->appRoot, '', $absolute_path);
  }

  /**
   * Determines if the given filename is relative.
   *
   * @param string $filename
   *   The filename.
   *
   * @return bool
   *   Whether or not the file is relative.
   */
  protected function isRelativeFile($filename) {
    return $filename[0] !== '/' && strpos($filename, '://') === FALSE;
  }

  /**
   * Gets dependencies for this component.
   *
   * @return array|null
   *   The dependencies for this component, or NULL if none is defined.
   */
  protected function getDependencies() {
    return $this::DEPENDENCIES;
  }

  /**
   * Determines whether or not this component defines attachments.
   *
   * @return bool
   *   Whether or not this component defines attachments.
   */
  protected function hasAttachments() {
    return $this::ATTACH || $this::DETACH;
  }

  /**
   * Determines whether or not this component defines JS.
   *
   * @return bool
   *   Whether or not this component defines JS.
   */
  protected function hasJs() {
    return (bool) $this::JS;
  }

  /**
   * Determines whether or not this component defines CSS.
   *
   * @return bool
   *   Whether or not this component defines CSS.
   */
  protected function hasCss() {
    return (bool) $this::CSS;
  }

  /**
   * Determines whether or not this component defines a library.
   *
   * @return bool
   *   Whether or not this component defines a library.
   */
  protected function hasLibraryData() {
    return (bool) $this::LIBRARY;
  }

  /**
   * Determines whether or not this component defines dependencies.
   *
   * @return bool
   *   Whether or not this component defines dependencies.
   */
  protected function hasDependencies() {
    return (bool) $this::DEPENDENCIES;
  }

  /**
   * Determines whether or not this component has assets.
   *
   * @return bool
   *   Whether or not this component defines assets.
   */
  protected function hasAssets() {
    return $this->hasCss() || $this->hasJs() || $this->hasAttachments();
  }

}
