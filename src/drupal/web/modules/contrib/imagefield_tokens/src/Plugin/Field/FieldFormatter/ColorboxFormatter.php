<?php

namespace Drupal\imagefield_tokens\Plugin\Field\FieldFormatter;

use Drupal\colorbox\ElementAttachmentInterface;
use Drupal\colorbox\Plugin\Field\FieldFormatter\ColorboxFormatter as ColorboxFormatterBase;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'imagefield_tokens_colorbox' formatter.
 *
 * @FieldFormatter(
 *   id = "imagefield_tokens_colorbox",
 *   label = @Translation("ImageField Tokens: Colorbox"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ColorboxFormatter extends ColorboxFormatterBase {

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenService;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   * @param \Drupal\colorbox\ElementAttachmentInterface $attachment
   *   Allow the library to be attached to the page.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler services.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $libraryDiscovery
   *   Library discovery service.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    AccountInterface $current_user,
    EntityStorageInterface $image_style_storage,
    ElementAttachmentInterface $attachment,
    ModuleHandlerInterface $moduleHandler,
    LibraryDiscoveryInterface $libraryDiscovery,
    Token $token
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage, $attachment, $moduleHandler, $libraryDiscovery);
    $this->tokenService = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('colorbox.attachment'),
      $container->get('module_handler'),
      $container->get('library.discovery'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $key => &$element) {
      if ($key !== '#attached') {
        $item = &$element['#item'];
        $item->alt = $this->tokenService->replace($item->alt, [], ['langcode' => $langcode]);
        $item->title = $this->tokenService->replace($item->title, [], ['langcode' => $langcode]);
      }
    }
    return $elements;
  }

}
