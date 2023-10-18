<?php

namespace Drupal\sfc;

/**
 * Contains helper methods for working with IDs.
 *
 * These methods are static to prevent component customization.
 */
class ComponentNameHelper {

  /**
   * Gets the library name for a component.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   The component.
   *
   * @return string
   *   The library name.
   */
  public static function getLibraryName(ComponentInterface $component) {
    return 'sfc/component.' . $component->getId();
  }

  /**
   * Checks if a library name comes from a component.
   *
   * @param string $name
   *   The library name.
   *
   * @return bool
   *   Whether or not the library name comes from a component.
   */
  public static function isComponentLibrary($name) {
    return strpos($name, 'sfc/component.') === 0;
  }

  /**
   * Gets a plugin ID from a library name.
   *
   * @param string $name
   *   The library name.
   *
   * @return string
   *   The plugin ID.
   */
  public static function getIdFromLibraryName($name) {
    return str_replace('sfc/component.', '', $name);
  }

  /**
   * Checks if a template name comes from a component.
   *
   * @param string $name
   *   The template name.
   *
   * @return bool
   *   Whether or not the template name comes from a component.
   */
  public static function isComponentTemplate($name) {
    return strpos($name, 'sfc--') === 0;
  }

  /**
   * Gets the template name for a component.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   The component.
   *
   * @return string
   *   The template name.
   */
  public static function getTemplateName(ComponentInterface $component) {
    return 'sfc--' . str_replace('_', '-', $component->getId());
  }

  /**
   * Gets an ID from a template name.
   *
   * @param string $name
   *   The template name.
   *
   * @return string
   *   The plugin ID.
   */
  public static function getIdFromTemplateName($name) {
    return str_replace(['sfc--', '.html.twig', '-'], ['', '', '_'], $name);
  }

}
