<?php

namespace Drupal\Tests\view_mode_switch\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\view_mode_switch\Traits\ViewModeSwitchTestTrait;

/**
 * Base class for view mode switch kernel tests.
 */
abstract class ViewModeSwitchTestBase extends FieldKernelTestBase {

  use StringTranslationTrait;
  use ViewModeSwitchTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['view_mode_switch'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Setup default test assets.
    $this->setupTestAssets();
  }

}
