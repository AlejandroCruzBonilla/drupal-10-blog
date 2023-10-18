<?php

namespace Drupal\Tests\sfc_example\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\sfc\Functional\FunctionalComponentTestTrait;

/**
 * Tests the ExampleJsTest component.
 *
 * @group sfc_example
 * @group functional
 *
 * @codeCoverageIgnore
 */
class ExampleJsTest extends WebDriverTestBase {

  use FunctionalComponentTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_example',
    'sfc_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the example JS component works.
   */
  public function testClickCounter() {
    $assert_session = $this->assertSession();
    $this->visitComponent('example_js', []);
    $assert_session->elementExists('css', '.example-js');
    $assert_session->pageTextContains('Clicked 0 times');
    $this->click('.example-js');
    $assert_session->pageTextContains('Clicked 1 times');
  }

}
