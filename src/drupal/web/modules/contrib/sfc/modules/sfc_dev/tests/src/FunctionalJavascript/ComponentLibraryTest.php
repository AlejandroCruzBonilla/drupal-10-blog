<?php

namespace Drupal\Tests\sfc_dev\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the component library.
 *
 * @group sfc_dev
 * @group functional
 *
 * @codeCoverageIgnore
 */
class ComponentLibraryTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
    'sfc_dev',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the component library works.
   */
  public function testLibrary() {
    $assert_session = $this->assertSession();
    $this->drupalGet('/sfc/library');
    $assert_session->pageTextContains('Access denied');
    $this->drupalLogin($this->drupalCreateUser(['use sfc dev'], 'Default'));
    $this->drupalGet('/sfc/library');
    $assert_session->pageTextNotContains('Access denied');
    $assert_session->elementExists('css', '.component-picker');
    $this->click('[data-component-picker-id="say_hello"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementExists('css', '.say-hello');
    $assert_session->pageTextContains('Hello Default!');
    $assert_session->elementExists('css', '.component-preview__input textarea')->setValue('{% include "sfc--say-hello.html.twig" with {name: "Sam"} %}');
    $this->click('.component-preview__input input[type="submit"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Hello Sam!');
    $assert_session->elementExists('css', '.component-preview__input textarea')->setValue('{% include "sfc--say-hello.html.twig" with {name: {"#foo": {}}} %}');
    $this->click('.component-preview__input input[type="submit"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('For security reasons, the "#" character and "convert_encoding" are not allowed.');
    $assert_session->elementExists('css', '.component-preview__input select[name="mode"]')->selectOption('form');
    $assert_session->elementExists('css', '.component-preview__input input[name="component_context[name]"]')->setValue('World');
    $this->click('.component-preview__input input[type="submit"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Hello World!');
    // Test auto reloading.
    $this->click('[data-component-picker-id="js_render"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('JS Render');
    $assert_session->fieldExists('auto_preview')->check();
    \Drupal::keyValue('sfc_test')->set('js_render', '$(this).text("New Render")');
    \Drupal::keyValue('sfc_test')->set('js_render_should_write', TRUE);
    $assert_session->waitForText('New Render');
    $assert_session->pageTextNotContains('JS Render');
    $assert_session->pageTextContains('New Render');
    \Drupal::keyValue('sfc_test')->set('js_render_should_write', FALSE);
  }

}
