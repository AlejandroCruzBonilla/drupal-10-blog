<?php

namespace Drupal\Tests\sfc\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides an integration test for basic component functionality.
 *
 * @group sfc
 * @group functional
 */
class ComponentTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'sfc',
    'sfc_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests basic component functionality.
   *
   * @codeCoverageIgnore
   */
  public function testComponents() {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->drupalCreateUser([], 'Test'));
    $this->drupalGet('/sfc_test/say_hello_default');
    $assert_session->pageTextContains('Hello Test!');
    $this->drupalGet('/sfc_test/say_hello_name');
    $assert_session->pageTextContains('Hello Sam!');
    $content = $this->getSession()->getPage()->getContent();
    // Ensure that assets are on page and in the filesystem.
    $this->assertStringContainsString($this->publicFilesDirectory . '/sfc/components/say_hello/say_hello.css', $content);
    $this->assertStringContainsString($this->publicFilesDirectory . '/sfc/components/say_hello/say_hello.js', $content);
    $this->assertTrue(file_exists('public://sfc/components/say_hello/say_hello.css'));
    $this->assertTrue(file_exists('public://sfc/components/say_hello/say_hello.js'));
    // Test theme overrides of templates.
    \Drupal::service('theme_installer')->install(['sfc_test_theme']);
    $this->config('system.theme')
      ->set('default', 'sfc_test_theme')
      ->save();
    $this->drupalGet('/sfc_test/say_hello_default');
    $assert_session->pageTextContains("OMG IT'S Test!");
  }

}
