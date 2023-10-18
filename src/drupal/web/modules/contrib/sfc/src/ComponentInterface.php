<?php

namespace Drupal\sfc;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface for single file components.
 */
interface ComponentInterface extends PluginInspectionInterface {

  /**
   * Gets the id of the component.
   *
   * This should only contain alphanumeric characters and underscores.
   *
   * @return string
   *   The id of the component.
   */
  public function getId();

  /**
   * The Twig template string.
   *
   * @return string
   *   The template.
   */
  public function getTemplate();

  /**
   * An array representing a Drupal library definition.
   *
   * @return array
   *   The library definition.
   */
  public function getLibrary();

  /**
   * Writes assets from the component class to the filesystem.
   */
  public function writeAssets();

  /**
   * Determines if assets should be written - i.e. if they're out of date.
   */
  public function shouldWriteAssets();

  /**
   * Allows components to modify context before it is used in their template.
   *
   * @param array &$context
   *   The context being passed to the Twig template.
   */
  public function prepareContext(array &$context);

}
