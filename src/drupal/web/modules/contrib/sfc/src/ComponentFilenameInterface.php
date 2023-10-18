<?php

namespace Drupal\sfc;

/**
 * An interface for single file components with filesystem files.
 */
interface ComponentFilenameInterface {

  /**
   * Gets the component filename/path.
   *
   * @return string
   *   The component filename/path.
   */
  public function getComponentFileName();

}
