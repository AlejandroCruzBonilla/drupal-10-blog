<?php

namespace Drupal\fvm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\fvm\Form
 */
class FvmSettingsForm extends ConfigFormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Entity Display Repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\fvm\Form\FvmSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   Bundle info.
   * @param \Drupal\Core\Entity\EntityDisplayRepository $entityDisplayRepository
   *   Entity Display Repository.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $bundleInfo, EntityDisplayRepository $entityDisplayRepository, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
    $this->entityDisplayRepository = $entityDisplayRepository;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_display.repository'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fvm.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fvm_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fvm.settings')->getRawData();
    $entityTypes = $this->entityTypeManager->getDefinitions();

    $form['help'] = [
      '#markup' => 'Entities and bundles without any additional view modes are not visible.',
    ];

    foreach ($entityTypes as $entityId => $entityType) {
      // Filters entities based on their view builder handlers.
      if (!$entityType->entityClassImplements(ContentEntityInterface::class)
        || !$entityType->get('field_ui_base_route')
        || !$entityType->hasViewBuilderClass()) {
        continue;
      }

      foreach ($this->bundleInfo->getBundleInfo($entityId) as $bundleId => $bundle) {
        $viewModes = $this->getViewModes($entityId, $bundleId);
        if (!empty($viewModes)) {
          $form[$entityId][$entityId . '__' . $bundleId] = [
            '#type' => 'checkbox',
            '#title' => $bundle['label'],
          ];
          if (!empty($config[$entityId . '__' . $bundleId])) {
            $form[$entityId][$entityId . '__' . $bundleId]['#default_value'] = $config[$entityId . '__' . $bundleId];
            $form[$entityId][$entityId . '__' . $bundleId]['#description'] = $this->t(
              'Warning: Unchecking this checkbox may result in data loss.
              If you want to remove this field from bundle, please hide it via form view mode.
              Refer README.txt of this module for more.'
            );
          }

          $form[$entityId][$entityId . '__' . $bundleId . '__view_modes'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('@bundleName View Modes', ['@bundleName' => $bundle['label']]),
            '#description' => $this->t('If no option is selected, all options are made available in the list.'),
            '#options' => $viewModes,
            '#states' => [
              'visible' => [
                'input[name="' . $entityId . '__' . $bundleId . '"]' => ['checked' => TRUE],
              ],
            ],
            '#prefix' => '<div class="layout-container">',
            '#suffix' => '</div>',
            '#default_value' => !empty($config[$entityId . '__' . $bundleId . '__view_modes']) ? $config[$entityId . '__' . $bundleId . '__view_modes'] : [],
          ];
        }
      }

      if (!empty($form[$entityId])) {
        $form[$entityId] = [
          '#type' => 'details',
          '#title' => (string) $entityType->getLabel(),
        ] + $form[$entityId];
      }

      // Additional option to override for custom block entity,
      // if layout manager is installed.
      if ($this->moduleHandler->moduleExists('layout_builder') && $entityId == 'block_content') {
        $form[$entityId]['hide_layout_builder_field'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Hide Layout builder view mode field.'),
          '#description' => $this->t('You can either show layout builder
          view mode field OR FVM view mode field, as both conflicts if used together.
          By Default, FVM will hide its field.'),
          '#default_value' => !empty($config['hide_layout_builder_field']) ? $config['hide_layout_builder_field'] : '',
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save configs.
    parent::submitForm($form, $form_state);
    $config = $this->config('fvm.settings');
    $values = $form_state->cleanValues()->getValues();
    foreach ($form_state->cleanValues()->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();

    unset($values['hide_layout_builder_field']);

    // Create and remove fields.
    foreach ($values as $key => $value) {
      // Do not process view mode values.
      if (strpos($key, '__view_modes') === FALSE) {
        list($entityName, $bundleName) = explode('__', $key);
        $fieldName = 'view_mode_selection';
        $field_storage = FieldStorageConfig::loadByName($entityName, $fieldName);

        if ((bool) $value) {
          if (!$field_storage) {
            $field_storage = FieldStorageConfig::create([
              'field_name' => $fieldName,
              'entity_type' => $entityName,
              'type' => 'entity_reference',
            ]);
            $field_storage->setSetting('target_type', 'entity_view_mode');
            $field_storage->setLocked(TRUE);
            $field_storage->save();
          }

          // Create the entity reference field if it doesn't already exist.
          $field = FieldConfig::loadByName($entityName, $bundleName, $fieldName);

          if (!$field) {
            $field = FieldConfig::create([
              'field_storage' => $field_storage,
              'bundle' => $bundleName,
            ]);
            $field->setSetting('handler', 'field_view_mode');
            $field->setLabel(t('View Mode'));
            $field->save();
          }

          $form_display = $this->entityTypeManager
            ->getStorage('entity_form_display')
            ->load($entityName . '.' . $bundleName . '.default');

          if (!$form_display->getComponent($fieldName)) {
            $form_display->setComponent($fieldName, [
              'type' => \Drupal::moduleHandler()
                ->moduleExists('options') ? 'fvm_options_select' : 'entity_reference_autocomplete',
              'region' => 'content',
            ])->save();
          }
        }
        else {
          if ($field_storage) {
            $query = \Drupal::database()
              ->select($entityName . '__' . $field_storage->getName(), $entityName . '__fvm')
              ->condition('bundle', $bundleName);
            $fieldIsUsed = (bool) $query->countQuery()->execute()->fetchField();

            if (!$fieldIsUsed && $field = FieldConfig::loadByName($entityName, $bundleName, $fieldName)) {
              $field->delete();
              field_purge_batch(10);

              $this->entityTypeManager
                ->getStorage('entity_form_display')
                ->load($entityName . '.' . $bundleName . '.default')
                ->removeComponent($fieldName)
                ->save();
            }

          }
        }
      }
    }
  }

  /**
   * Returns an array of view modes of an entity by bundle.
   *
   * @param string $entityId
   *   The entity type whose view mode options should be returned.
   * @param string $bundleId
   *   The name of the bundle.
   *
   * @return array
   *   An array of view mode labels, keyed by the display mode ID.
   */
  protected function getViewModes($entityId, $bundleId) {
    $allViewModes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entityId, $bundleId);

    // Unset Default view mode, as anyhow, we default to this view mode.
    unset($allViewModes['default']);

    // Don't process if default is the only view mode.
    if (!empty($allViewModes)) {
      $viewModes = [];
      foreach ($allViewModes as $id => $viewMode) {
        $viewModes[$id] = (string) $viewMode;
      }
      return $viewModes;
    }
    return [];
  }

  /**
   * Process callback to modify the layout builder block update form.
   *
   * @param array $element
   *   The containing element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The containing element, with the form modified.
   */
  public static function processBlockForm(array $element, FormStateInterface $form_state) {
    $config = \Drupal::config('fvm.settings')->getRawData();
    if ((bool) $config['hide_layout_builder_field'] === FALSE) {
      $element['view_mode_selection']['#access'] = FALSE;
    }
    return $element;
  }

}
