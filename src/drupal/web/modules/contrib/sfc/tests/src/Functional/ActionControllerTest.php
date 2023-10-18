<?php

namespace Drupal\Tests\sfc\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests methods provided by the action controller.
 *
 * @group sfc
 * @group functional
 */
class ActionControllerTest extends BrowserTestBase {

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
   * Tests the ::perform method.
   *
   * @codeCoverageIgnore
   */
  public function testPerform() {
    $user = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($user);

    $url = new Url('sfc.action', [
      'component_id' => 'simple_actions',
      'action' => 'json_response',
    ]);
    $url->setOption('query', ['token' => \Drupal::getContainer()->get('csrf_token')->get($url->getInternalPath())]);
    $url->setAbsolute();
    $this->drupalGet($url->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains(json_encode(['hello' => 'world']));

    $url = new Url('sfc.action', [
      'component_id' => 'simple_actions',
      'action' => 'render_response',
    ]);
    $url->setOption('query', ['token' => \Drupal::getContainer()->get('csrf_token')->get($url->getInternalPath())]);
    $url->setAbsolute();
    $this->drupalGet($url->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Hello world!');

    $url->setOption('query', ['token' => '']);
    $this->drupalGet($url->toString());
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalLogin(AccountInterface $account) {
    parent::drupalLogin($account);
    $session_data = $this->container->get('session_handler.write_safe')->read($this->getSession()->getCookie($this->getSessionName()));
    $csrf_token_seed = unserialize(explode('_sf2_meta|', $session_data)[1])['s'];
    $this->container->get('session_manager.metadata_bag')->setCsrfTokenSeed($csrf_token_seed);
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalLogout() {
    parent::drupalLogout();
    $this->container->get('session_manager.metadata_bag')->stampNew();
  }

}
