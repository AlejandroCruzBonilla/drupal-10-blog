<?php

namespace Drupal\Tests\sfc_example\Nightwatch;

use Drupal\TestSite\TestSetupInterface;

/**
 * Example setup file for Nighwatch tests.
 *
 * @codeCoverageIgnore
 */
class ExampleNightwatchSetup implements TestSetupInterface {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    \Drupal::service('module_installer')->install([
      'sfc',
      'sfc_test',
      'sfc_example',
    ]);
  }

}
