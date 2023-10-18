<?php

namespace Drupal\sfc_dev\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\sfc\ComponentInterface;
use Drupal\sfc\ComponentNameHelper;

/**
 * AJAX command to reload component assets.
 */
class RefreshComponentAssetsCommand implements CommandInterface {

  /**
   * The component.
   *
   * @var \Drupal\sfc\ComponentInterface
   */
  protected $component;

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * Constructs a RefreshComponentAssetsCommand object.
   *
   * @param \Drupal\sfc\ComponentInterface $component
   *   The component.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   */
  public function __construct(ComponentInterface $component, AssetResolverInterface $asset_resolver) {
    $this->component = $component;
    $this->assetResolver = $asset_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->component->shouldWriteAssets()) {
      $this->component->writeAssets();
    }
    $library_name = ComponentNameHelper::getLibraryName($this->component);
    $assets = new AttachedAssets();
    $assets->setLibraries([$library_name]);
    $css_assets = $this->assetResolver->getCssAssets($assets, FALSE);
    list($js_assets_header, $js_assets_footer) = $this->assetResolver->getJsAssets($assets, FALSE);
    $js_assets_header = $js_assets_header ? $js_assets_header : [];
    $js_assets_footer = $js_assets_footer ? $js_assets_footer : [];
    $css_assets = $css_assets ? $css_assets : [];
    $assets = array_merge(array_keys($css_assets), array_keys($js_assets_header), array_keys($js_assets_footer));
    $assets = array_diff($assets, ['drupalSettings']);
    // We can't know exactly what assets are related to a component, but we
    // know that core will never be and removing them fixes JS bugs.
    foreach ($assets as $i => $asset) {
      if (strpos($asset, 'core/') === 0) {
        unset($assets[$i]);
      }
    }
    return [
      'command' => 'sfc_refresh_component_assets',
      'assets' => array_values($assets),
    ];
  }

}
