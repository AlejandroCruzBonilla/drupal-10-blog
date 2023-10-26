<?php

namespace Drupal\view_mode_switch;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\view_mode_switch\Entity\EntityFieldManagerInterface;

/**
 * Provides the view mode switch service.
 */
class ViewModeSwitch implements ViewModeSwitchInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * Constructs a new ViewModeSwitch.
   *
   * @param \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface $view_mode_switch_field_manager
   *   The view mode switch entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   */
  public function __construct(EntityFieldManagerInterface $view_mode_switch_field_manager, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->viewModeSwitchEntityFieldManager = $view_mode_switch_field_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *
   * @see \Drupal\view_mode_switch\ViewModeSwitch::doGetViewModeToSwitchTo()
   */
  public function getViewModeToSwitchTo(FieldableEntityInterface $entity, $view_mode): ?string {
    /** @var array $cache */
    $cache = &drupal_static(__METHOD__, []);

    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    // Prepare cache ID.
    $cid = implode(':', [
      $entity_type_id,
      $bundle,
      $entity->id() ?: $entity->uuid(),
      $view_mode,
    ]);

    // Cached data exists?
    if (isset($cache[$cid])) {
      return $cache[$cid] ?: NULL;
    }

    // Determine view mode to switch to (if any).
    $cache[$cid] = $this->doGetViewModeToSwitchTo($entity, $view_mode) ?: FALSE;

    return $cache[$cid] ?: NULL;
  }

  /**
   * Internal method to get the view mode to switch to (if applicable).
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to process for view mode switches.
   * @param string $view_mode
   *   The origin view mode.
   * @param array $results
   *   (internal) Used to track result information for all processed items.
   *
   * @return string|null
   *   The name of the view mode to switch to.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *
   * @see \Drupal\view_mode_switch\ViewModeSwitch::getViewModeToSwitchTo()
   */
  protected function doGetViewModeToSwitchTo(FieldableEntityInterface $entity, string $view_mode, array $results = []): ?string {
    if (!$results) {
      $results = [
        'origin_view_mode' => $view_mode,
        'view_mode_switches' => [],
      ];
    }

    // Switch view mode (if applicable).
    if (($field = $this->viewModeSwitchEntityFieldManager->getApplicableField($entity, $view_mode))) {
      $field_name = $field->getName();
      $target_view_mode = $field->getViewMode();

      // Recursion detected?
      if (isset($results['view_mode_switches'][$field_name])) {
        $this->logger->get('view_mode_switch')
          ->warning('Recursion detected when trying to switch %origin_view_mode view mode via %view_mode_switches.', [
            '%origin_view_mode' => $results['origin_view_mode'],
            '%view_mode_switches' => implode(' › ', [$results['origin_view_mode']] + $results['view_mode_switches'] + [$view_mode]),
            'link' => $entity->toLink($this->t('View'))->toString(),
          ]);

        // Return last view mode before recursion.
        return $results['view_mode_switches'][$field_name];
      }

      // Mark field as processed (for later recursion detection).
      $results['view_mode_switches'][$field_name] = $target_view_mode;

      // Merge entity view mode cache metadata into given entity.
      $entity_view_mode = $this->entityTypeManager
        ->getStorage('entity_view_mode')
        ->load($entity->getEntityTypeId() . '.' . $target_view_mode);

      if ($entity_view_mode instanceof EntityViewModeInterface) {
        $entity->addCacheableDependency($entity_view_mode);
      }

      // Check if there are subsequent view mode switches available for the view
      // mode currently switched to.
      if (($subsequent_view_mode = $this->doGetViewModeToSwitchTo($entity, $target_view_mode ?? '', $results))) {
        return $subsequent_view_mode;
      }

      return $target_view_mode;
    }

    return NULL;
  }

}
