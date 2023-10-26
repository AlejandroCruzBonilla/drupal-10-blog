<?php

namespace Drupal\view_mode_switch\Entity;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\view_mode_switch\ViewModeHelperInterface;

/**
 * View mode switch related helper methods for entity view mode delete forms.
 */
class EntityViewModeDeleteFormHelper implements EntityViewModeDeleteFormHelperInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The view mode switch entity field manager.
   *
   * @var \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface
   */
  protected $viewModeSwitchEntityFieldManager;

  /**
   * The view mode helper service.
   *
   * @var \Drupal\view_mode_switch\ViewModeHelperInterface
   */
  protected $viewModeHelper;

  /**
   * Constructs a new EntityViewModeDeleteFormHelper.
   *
   * @param \Drupal\view_mode_switch\ViewModeHelperInterface $view_mode_helper
   *   The view mode helper service.
   * @param \Drupal\view_mode_switch\Entity\EntityFieldManagerInterface $view_mode_switch_field_manager
   *   The view mode switch entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ViewModeHelperInterface $view_mode_helper, EntityFieldManagerInterface $view_mode_switch_field_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->viewModeHelper = $view_mode_helper;
    $this->viewModeSwitchEntityFieldManager = $view_mode_switch_field_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function alter(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();

    // Is entity form?
    if (!($form_object instanceof EntityFormInterface)) {
      throw new \LogicException('Required form alters for for potential field storage configuration updates may only be applied to entity forms.');
    }

    /** @var \Drupal\Core\Entity\EntityViewModeInterface $entity */
    $entity = $form_object->getEntity();

    // Entity is view mode?
    if (!($entity instanceof EntityViewModeInterface)) {
      throw new \LogicException('Required form alters for potential field storage configuration updates may only be applied to view mode entity forms.');
    }

    // Is 'delete' operation form?
    if ($form_object->getOperation() !== 'delete') {
      throw new \LogicException('Required form alters for potential field storage configuration updates may only be applied to view mode entity delete forms.');
    }

    // Determine view mode name.
    $view_mode = $this->viewModeHelper->getName($entity);

    // Field storage configurations exist that use deleted view mode as origin
    // view mode?
    if (($field_storages = $this->viewModeSwitchEntityFieldManager->getFieldStorageDefinitionsUsingOriginViewMode($view_mode))) {
      // Ensure entity updates are visible.
      $form['entity_updates']['#access'] = TRUE;

      // Determine entity type ID.
      $entity_type_id = reset($field_storages)->getEntityTypeId();

      // Load entity type definition.
      if (($entity_type = $this->entityTypeManager->getDefinition($entity_type_id)) && $entity_type instanceof EntityTypeInterface) {
        $entity_type_label = $entity_type->getLabel();

        foreach ($field_storages as $field_storage) {
          // Ensure item list for field storage configuration updates.
          if (!isset($form['entity_updates'][$entity_type_id])) {
            $form['entity_updates'][$entity_type_id] = [
              '#theme' => 'item_list',
              '#title' => $entity_type_label,
              '#items' => [],
            ];
          }

          // Add field storage using deleted view mode as origin view mode to
          // update list.
          $form['entity_updates'][$entity_type_id]['#items'][$field_storage->id()] = $field_storage->label() ?: $field_storage->id();
        }
      }
    }
  }

}
