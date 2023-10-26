<?php

namespace Drupal\Tests\view_mode_switch\FunctionalJavascript;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\view_mode_switch\Traits\ViewModeSwitchTestTrait;

/**
 * Base class for functional view mode switch JavaScript tests.
 */
abstract class ViewModeSwitchTestBase extends WebDriverTestBase {

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
    'entity_test',
    'view_mode_switch',
  ];

}
