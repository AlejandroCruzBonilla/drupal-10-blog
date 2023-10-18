<?php

namespace Drupal\sfc\Plugin\SingleFileComponent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\sfc\ComponentBase;
use Drupal\sfc\Plugin\Derivative\SimpleComponentDeriver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Contains a component that renders based on the contents of a .sfc file.
 *
 * @SingleFileComponent(
 *   id = "sfc_simple_component",
 *   deriver = "\Drupal\sfc\Plugin\Derivative\SimpleComponentDeriver",
 * )
 */
class SimpleComponent extends ComponentBase {

  /**
   * An array of file data.
   *
   * @var array
   */
  protected $fileData;

  /**
   * Loads, parses, and returns simple file data.
   *
   * @return array
   *   An array containing keys that match up with ComponentBase's constants.
   */
  protected function getFileData() {
    if (!empty($this->fileData)) {
      return $this->fileData;
    }

    $contents = sfc_require($this->pluginDefinition['simple_file']);
    // These default values mimic ComponentBase constants/methods.
    $new_data = [
      'JS' => NULL,
      'ATTACH' => NULL,
      'DETACH' => NULL,
      'VANILLA_JS' => FALSE,
      'CSS' => NULL,
      'TEMPLATE' => '',
      'SELECTOR' => NULL,
      'DEPENDENCIES' => NULL,
      'LIBRARY' => NULL,
      'prepareContext' => NULL,
      'buildContextForm' => NULL,
      'validateContextForm' => NULL,
      'submitContextForm' => NULL,
      'actions' => [],
    ];
    // Parse HTML content of file.
    $this->parseFileHtml($contents['content'], $new_data);
    // Parse variables set with PHP.
    if (isset($contents['selector'])) {
      $new_data['SELECTOR'] = $contents['selector'];
    }
    if (isset($contents['dependencies'])) {
      $new_data['DEPENDENCIES'] = $contents['dependencies'];
    }
    if (isset($contents['library'])) {
      $new_data['LIBRARY'] = $contents['library'];
    }
    $callbacks = [
      'prepareContext',
      'buildContextForm',
      'validateContextForm',
      'submitContextForm',
    ];
    foreach ($callbacks as $callback) {
      if (isset($contents[$callback]) && is_callable($contents[$callback])) {
        $new_data[$callback] = $contents[$callback];
      }
    }
    if (isset($contents['actions']) && is_array($contents['actions'])) {
      $new_data['actions'] = $contents['actions'];
    }
    $this->fileData = $new_data;
    return $this->fileData;
  }

  /**
   * Parses the HTML content of the .sfc file.
   *
   * @param string $content
   *   The HTML content of an .sfc file.
   * @param array $file_data
   *   The file data array.
   */
  protected function parseFileHtml($content, array &$file_data) {
    if (preg_match('/^<script>([\s\S]+?)^<\/script>/im', $content, $matches)) {
      $file_data['JS'] = $matches[1];
    }
    if (preg_match('/^<script[^>]*data-type="attach"[^>]*>([\s\S]+?)^<\/script>/im', $content, $matches)) {
      $file_data['ATTACH'] = $matches[1];
    }
    if (preg_match('/^<script[^>]*data-type="detach"[^>]*>([\s\S]+?)^<\/script>/im', $content, $matches)) {
      $file_data['DETACH'] = $matches[1];
    }
    if (preg_match('/^<script[^>]*data-vanilla[^>]*>/im', $content)) {
      $file_data['VANILLA_JS'] = TRUE;
    }
    if (preg_match('/^<style[^>]*>([\s\S]+?)^<\/style>/im', $content, $matches)) {
      $file_data['CSS'] = $matches[1];
    }
    if (preg_match('/^<template[^>]*>([\s\S]+?)^<\/template>/im', $content, $matches)) {
      $file_data['TEMPLATE'] = trim($matches[1], "\n");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    $context['sfc_unique_id'] = self::determineUniqueId($context);
    $attributes = new Attribute();
    $attributes->setAttribute('data-sfc-id', $this->getId());
    $attributes->setAttribute('data-sfc-unique-id', $context['sfc_unique_id']);
    $context['sfc_attributes'] = $attributes;
    $data = $this->getFileData();
    $action_ids = array_keys($data['actions']);
    foreach ($action_ids as $name) {
      $url = new Url('sfc.action', [
        'component_id' => $this->getId(),
        'action' => $name,
      ]);
      $url->setOption('query', ['sfc_unique_id' => $context['sfc_unique_id']]);
      $context['sfc_actions'][$name] = $url;
    }
    if ($data['prepareContext']) {
      $this->autowireCall($data['prepareContext'], $this->pluginDefinition['callback_autowire']['prepareContext'], [&$context]);
    }
  }

  /**
   * Calls a callback using dependency injection, similar to Symfony Autowiring.
   *
   * @param callable $callback
   *   The callback.
   * @param array $autowire_args
   *   An array mapping argument indexes to service IDs.
   * @param array $defaults
   *   An array mapping argument indexes or service IDs to default values.
   *
   * @return mixed
   *   The return value of $callback.
   */
  protected function autowireCall(callable $callback, array $autowire_args, array $defaults) {
    if (!isset($defaults[SimpleComponentDeriver::AUTOWIRE_CURRENT_REQUEST])) {
      $defaults[SimpleComponentDeriver::AUTOWIRE_CURRENT_REQUEST] = \Drupal::request();
    }
    $args = [];
    foreach ($autowire_args as $i => $service_id) {
      if (isset($defaults[$i])) {
        $args[$i] = &$defaults[$i];
      }
      elseif (isset($defaults[$service_id])) {
        $args[$i] = $defaults[$service_id];
      }
      else {
        $args[$i] = \Drupal::getContainer()->get($service_id);
      }
    }
    return call_user_func_array($callback, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function performAction($name, Request $request) {
    $data = $this->getFileData();
    if (isset($data['actions'][$name]) && is_callable($data['actions'][$name])) {
      return $this->autowireCall($data['actions'][$name], $this->pluginDefinition['action_autowire'][$name], [SimpleComponentDeriver::AUTOWIRE_CURRENT_REQUEST => $request]);
    }
    throw new NotFoundHttpException('Action not found');
  }

  /**
   * {@inheritdoc}
   */
  protected function getAttachmentData() {
    $data = $this->getFileData();
    return [
      'selector' => $this::SELECTOR ? $this::SELECTOR : $this->getFallBackSelector(),
      'attach' => $data['ATTACH'],
      'detach' => $data['DETACH'],
      'vanilla_js' => $data['VANILLA_JS'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getCss() {
    return $this->replaceCssPaths($this->getFileData()['CSS']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getJs() {
    return $this->getFileData()['JS'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDependencies() {
    return $this->getFileData()['DEPENDENCIES'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getTemplateData() {
    return $this->getFileData()['TEMPLATE'];
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentFileName() {
    return $this->pluginDefinition['simple_file'];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasDependencies() {
    return (bool) $this->getFileData()['DEPENDENCIES'];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasAttachments() {
    $data = $this->getFileData();
    return $data['ATTACH'] || $data['DETACH'];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasCss() {
    return (bool) $this->getFileData()['CSS'];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasJs() {
    return (bool) $this->getFileData()['JS'];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasLibraryData() {
    return (bool) $this->getFileData()['LIBRARY'] || !empty($this->getLocalLibraryData());
  }

  /**
   * {@inheritdoc}
   */
  protected function getLibraryData() {
    $local_library = $this->getLocalLibraryData();
    $library = $this->getFileData()['LIBRARY'];

    if ($library) {
      if (isset($library['css'])) {
        foreach ($library['css'] as &$files) {
          $this->processLibraryFiles($files);
        }
      }
      if (isset($library['js'])) {
        $this->processLibraryFiles($library['js']);
      }
    }
    else {
      $library = [];
    }

    if ($local_library) {
      if (!empty($library['dependencies'])) {
        unset($local_library['dependencies']);
      }
      $library = array_merge_recursive($library, $local_library);
    }

    return $library;
  }

  /**
   * Returns a library definition if <plugin_id>.<css|js> files exist.
   *
   * @return array
   *   A library definition.
   */
  protected function getLocalLibraryData() {
    $parts = pathinfo($this->getComponentFileName());
    $prefix = $parts['dirname'] . '/' . $parts['filename'];
    $css = $prefix . '.css';
    $js = $prefix . '.js';
    $library = [];
    if (file_exists($css)) {
      $relative_css = str_replace($this->appRoot, '', $css);
      $library['css'] = [
        'base' => [
          $relative_css => [],
        ],
      ];
    }
    if (file_exists($js)) {
      $relative_js = str_replace($this->appRoot, '', $js);
      $library['js'] = [
        $relative_js => [],
      ];
      $contents = file_get_contents($js);
      $dependencies = [];
      $depmap = [
        'once(' => 'core/once',
        'jQuery' => 'core/jquery',
        'Drupal.ajax' => 'core/drupal.ajax',
        'Drupal.Ajax' => 'core/drupal.ajax',
        'drupalSettings' => 'core/drupalSettings',
        'Drupal.' => 'core/drupal',
      ];
      foreach ($depmap as $search => $dep) {
        if (strpos($contents, $search) !== FALSE) {
          $dependencies[] = $dep;
        }
      }
      $library['dependencies'] = $dependencies;
    }
    return $library;
  }

  /**
   * {@inheritdoc}
   */
  public function buildContextForm(array $form, FormStateInterface $form_state, array $default_values = []) {
    if ($callback = $this->getFileData()['buildContextForm']) {
      $form = $this->autowireCall($callback, $this->pluginDefinition['callback_autowire']['buildContextForm'], [
        $form,
        $form_state,
        $default_values,
      ]);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContextForm(array &$form, FormStateInterface $form_state) {
    if ($callback = $this->getFileData()['validateContextForm']) {
      $this->autowireCall($callback, $this->pluginDefinition['callback_autowire']['validateContextForm'], [
        $form,
        $form_state,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitContextForm(array &$form, FormStateInterface $form_state) {
    if ($callback = $this->getFileData()['submitContextForm']) {
      $this->autowireCall($callback, $this->pluginDefinition['callback_autowire']['submitContextForm'], [
        $form,
        $form_state,
      ]);
    }
  }

  /**
   * Determines the unique ID for a component.
   *
   * @param array $context
   *   The context being passed to the Twig template.
   *
   * @return string
   *   The unique ID.
   */
  protected function determineUniqueId(array $context) {
    $unique_ids = &drupal_static(__FUNCTION__, []);

    // Parent components may unintentionally pass their unique IDs to children.
    $id = !isset($context['sfc_unique_id']) || in_array($context['sfc_unique_id'], $unique_ids, TRUE) ? uniqid($this->getId()) : $context['sfc_unique_id'];

    $unique_ids[] = $id;
    return $id;
  }

}
