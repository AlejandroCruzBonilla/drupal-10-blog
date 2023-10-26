<?php

namespace Drupal\Tests\view_mode_switch\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\view_mode_switch\Traits\ViewModeSwitchTestTrait;

/**
 * Base class for functional view mode switch tests.
 */
abstract class ViewModeSwitchTestBase extends BrowserTestBase {

  use StringTranslationTrait;
  use ViewModeSwitchTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'entity_test',
    'field_ui',
    'view_mode_switch',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('system_breadcrumb_block');
  }

}
