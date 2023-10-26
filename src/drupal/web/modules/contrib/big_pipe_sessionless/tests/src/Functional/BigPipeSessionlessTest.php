<?php

namespace Drupal\Tests\big_pipe_sessionless\Functional;

use Drupal\big_pipe\Render\BigPipe;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Tests\big_pipe\Functional\BigPipeTest;

/**
 * Tests BigPipe's sessionless support.
 *
 * Extends \Drupal\big_pipe\Tests\BigPipeTest, to ensure that we also run "core"
 * BigPipe's tests, to guarantee that we don't break it.
 *
 * @group big_pipe_sessionless
 * @covers \Drupal\big_pipe_sessionless\Render\Placeholder\BigPipeSessionlessStrategy
 * @covers \Drupal\big_pipe_sessionless\EventSubscriber\HtmlResponseBigPipeSessionlessSubscriber
 * @covers \Drupal\big_pipe_sessionless\Render\BigPipeSessionless
 * @covers \Drupal\big_pipe_sessionless\PageCache\ResponsePolicy\DenyBigPipeSessionlessResponses
 * @covers \Drupal\big_pipe_sessionless\StackMiddleware\BigPipeSessionlessPageCache
 * @covers \Drupal\big_pipe_sessionless\BigPipeSessionlessServiceProvider
 */
class BigPipeSessionlessTest extends BigPipeTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'big_pipe_sessionless',
    'big_pipe_sessionless_test',
  ];

  /**
   * The database connection for testing.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->connection = Database::getConnection();
  }

  /**
   * Tests BigPipe-delivered HTML responses for requests with no session.
   *
   * Covers:
   * - \Drupal\big_pipe\EventSubscriber\HtmlResponseBigPipeSubscriber
   * - \Drupal\big_pipe\Render\BigPipe
   * - \Drupal\big_pipe\Render\BigPipe::sendNoJsPlaceholders()
   *
   * @see \Drupal\big_pipe_test\BigPipePlaceholderTestCases
   */
  public function testBigPipeNoSession() {
    $cases = [
      'default' => [
        'configured max-age' => 0,
        'page cache hit cache control' => 'must-revalidate, no-cache, private',
      ],
      'max-age=300' => [
        'configured max-age' => 300,
        'page cache hit cache control' => 'max-age=300, public',
      ],
    ];

    foreach ($cases as $case => $expectations) {
      // Simulate production.
      $this->config('system.logging')->set('error_level', ERROR_REPORTING_HIDE)->save();
      $this->config('system.performance')->set('cache.page.max_age', $expectations['configured max-age'])->save();

      $this->assertSessionCookieExists(FALSE);
      $this->assertBigPipeNoJsCookieExists(FALSE);

      // First request: Page Cache miss, streamed response by BigPipe.
      $this->drupalGet(Url::fromRoute('big_pipe_sessionless_test'));
      $this->assertNull($this->getSession()->getResponseHeader('X-Drupal-Cache'), 'No X-Drupal-Cache header.');
      $this->assertBigPipeResponseHeadersPresent();
      $this->assertSession()->responseHeaderNotContains('X-Drupal-Cache-Tags', 'cache_tag_set_in_lazy_builder');
      $this->assertSession()->responseContains('<a href="' . base_path() . 'big_pipe_sessionless_test" data-drupal-link-system-path="big_pipe_sessionless_test" class="is-active">This should be marked active</a>');
      $this->assertSession()->responseContains('<a href="' . base_path() . '" data-drupal-link-system-path="&lt;front&gt;">This should be marked inactive</a>');

      $cases = $this->getTestCases();
      $this->assertBigPipeNoJsPlaceholders([
        $cases['edge_case__invalid_html']->bigPipeNoJsPlaceholder => $cases['edge_case__invalid_html']->embeddedHtmlResponse,
        $cases['html_attribute_value']->bigPipeNoJsPlaceholder => '<form class="big-pipe-test-form" data-drupal-selector="big-pipe-test-form" action="' . base_path() . 'big_pipe_sessionless_test"',
        $cases['html']->bigPipeNoJsPlaceholder => NULL,
        $cases['edge_case__html_non_lazy_builder']->bigPipeNoJsPlaceholder => $cases['edge_case__html_non_lazy_builder']->embeddedHtmlResponse,
        $cases['exception__lazy_builder']->bigPipePlaceholderId => NULL,
        $cases['exception__embedded_response']->bigPipePlaceholderId => NULL,
      ]);

      // Verifying there are no BigPipe placeholders & replacements.
      $this->assertEquals('<none>', $this->getSession()->getResponseHeader('BigPipe-Test-Placeholders'));
      // BigPipe start signal absent.
      $this->assertSession()->responseNotContains(BigPipe::START_SIGNAL);
      // BigPipe stop signal absent.
      $this->assertSession()->responseNotContains(BigPipe::STOP_SIGNAL);

      // Verifying BigPipe assets are absent.
      $this->assertTrue(empty($this->getDrupalSettings()), 'drupalSettings and BigPipe asset library absent.');

      // Closing body tag present.
      $this->assertSession()->responseContains('</body>');

      // Repeat request: Page Cache hit, BigPipe not involved.
      $this->drupalGet(Url::fromRoute('big_pipe_sessionless_test'));
      $this->assertSame('HIT', $this->getSession()->getResponseHeader('X-Drupal-Cache'), 'Page cache hit.');
      $this->assertSame($expectations['page cache hit cache control'], $this->getSession()->getResponseHeader('Cache-Control'));
      $this->assertNull($this->getSession()->getResponseHeader('Surrogate-Control'), 'No Surrogate-Control header.');
      $this->assertNull($this->getSession()->getResponseHeader('X-Accel-Buffering'), 'No X-Accel-Buffering header.');
      $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'cache_tag_set_in_lazy_builder');
      $this->assertSession()->responseContains('<a href="' . base_path() . 'big_pipe_sessionless_test" data-drupal-link-system-path="big_pipe_sessionless_test" class="is-active">This should be marked active</a>');
      $this->assertSession()->responseContains('<a href="' . base_path() . '" data-drupal-link-system-path="&lt;front&gt;">This should be marked inactive</a>');

      // Verify that page cache request policies are respected.
      // First request: Page Cache miss, streamed response by BigPipe.
      $page_cache_items_before = array_column($this->connection->query("SELECT * FROM {cache_page};", [], ['fetch' => \PDO::FETCH_ASSOC])->fetchAll(), 'cid');
      $this->drupalGet(Url::fromRoute('big_pipe_sessionless_test', ['silly' => 'yes']));
      $this->assertNull($this->getSession()->getResponseHeader('X-Drupal-Cache'), 'Page cache miss.');
      $page_cache_items_after = array_column($this->connection->query("SELECT * FROM {cache_page};", [], ['fetch' => \PDO::FETCH_ASSOC])->fetchAll(), 'cid');
      // Repeat request: Page Cache hit, BigPipe not involved.
      $this->drupalGet(Url::fromRoute('big_pipe_sessionless_test', ['silly' => 'yes']));
      $this->assertNull($this->getSession()->getResponseHeader('X-Drupal-Cache'), 'Page cache miss.');
      // Ensure no no Page Cache item was created (not serving it is not
      // sufficient: it should not even be created).
      $this->assertSame($page_cache_items_before, $page_cache_items_after);

      // Clear the Page Cache. Note that we use a cache tag that exists on this
      // response, despite it being absent from both the Page Cache miss
      // response (because it was streamed) and the Page Cache hit response
      // (because it is identical to the original Page Cache miss response,
      // minus the streaming).
      Cache::invalidateTags(['cache_tag_set_in_lazy_builder']);

      // Simulate development: verifying BigPipe provides useful error output
      // when an error occurs while rendering a placeholder if verbose error
      // logging is enabled.
      $this->config('system.logging')->set('error_level', ERROR_REPORTING_DISPLAY_VERBOSE)->save();
      $this->drupalGet(Url::fromRoute('big_pipe_sessionless_test'));
      // The 'edge_case__html_exception' case throws an exception.
      $this->assertSession()->responseContains('The website encountered an unexpected error.');
      $this->assertSession()->responseContains('You are not allowed to say llamas are not cool!');
      // Closing body tag absent: error occurred before then.
      $this->assertSession()->responseNotContains('</body>');
      // The exception is expected. Do not interpret it as a test failure.
      unlink(\Drupal::root() . '/' . $this->siteDirectory . '/error.log');

      // Clear the Page Cache.
      Cache::invalidateTags(['rendered']);
    }
  }

}
