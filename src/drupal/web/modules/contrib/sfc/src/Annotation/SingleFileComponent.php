<?php

namespace Drupal\sfc\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for single file components.
 *
 * @Annotation
 */
class SingleFileComponent extends Plugin {

  /**
   * The ID of the plugin.
   *
   * Should be unique. Cannot contain dashes.
   *
   * @var string
   */
  public $id;

  /**
   * Aliases used to load this component's template.
   *
   * Note that these do not have to contain ".html.twig".
   *
   * @var array
   */
  public $aliases;

  /**
   * Theme hooks to override.
   *
   * For example, this would override a node field:
   * ["field__node__body__article"].
   *
   * @var array
   */
  public $overrides;

}
