<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldType;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'view_mode_switch' field type.
 *
 * @FieldType(
 *   id = "view_mode_switch",
 *   label = @Translation("View mode switch"),
 *   description = @Translation("An entity field allowing one or more view modes to be switched."),
 *   category = @Translation("Layout"),
 *   default_widget = "view_mode_switch",
 *   default_formatter = "view_mode_switch_default",
 *   list_class = "\Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemList",
 *   cardinality = 1,
 * )
 */
class ViewModeSwitchItem extends FieldItemBase implements ViewModeSwitchItemInterface {

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * {@inheritdoc}
   */
  public static function calculateDependencies(FieldDefinitionInterface $field_definition): array {
    $dependencies = parent::calculateDependencies($field_definition);

    // Depend on configured allowed view modes.
    if (($allowed_view_modes = $field_definition->getSetting('allowed_view_modes')) && is_array($allowed_view_modes)) {
      $allowed_view_modes = array_map(function ($view_mode) use ($field_definition) {
        return implode('.', [
          $field_definition->getTargetEntityTypeId(),
          $view_mode,
        ]);
      }, array_keys(array_filter($allowed_view_modes)));

      // Load allowed view mode entities.
      $allowed_view_mode_entities = \Drupal::entityTypeManager()
        ->getStorage('entity_view_mode')
        ->loadMultiple($allowed_view_modes);

      // Add allowed view modes as dependencies.
      if ($allowed_view_mode_entities) {
        foreach ($allowed_view_mode_entities as $allowed_view_mode_entity) {
          $dependencies[$allowed_view_mode_entity->getConfigDependencyKey()][] = $allowed_view_mode_entity->getConfigDependencyName();
        }
      }
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    return [
      'allowed_view_modes' => [],
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings(): array {
    return [
      'origin_view_modes' => [],
    ] + parent::defaultStorageSettings();
  }

  /**
   * Returns the entity display repository.
   *
   * @return \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   *   The entity display repository.
   */
  protected function entityDisplayRepository(): EntityDisplayRepositoryInterface {
    if (!($this->entityDisplayRepository instanceof EntityDisplayRepositoryInterface)) {
      $this->entityDisplayRepository = \Drupal::service('entity_display.repository');
    }

    return $this->entityDisplayRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state): array {
    $element = parent::fieldSettingsForm($form, $form_state);

    // View modes allowed to switch to.
    $element['allowed_view_modes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('View modes allowed to switch to'),
      '#description' => $this->t('Select the views modes allowed to switch the origin view mode(s) to.'),
      '#options' => $this->getSettableOptions(NULL, FALSE),
      '#default_value' => $this->getSetting('allowed_view_modes') ?: [],
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function fieldSettingsToConfigData(array $settings): array {
    // Filter empty values from allowed view modes.
    if (!empty($settings['allowed_view_modes'])) {
      $settings['allowed_view_modes'] = array_filter($settings['allowed_view_modes']);
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedViewModes(): array {
    // @todo Use computed property instead.
    $allowed_view_modes = $this->getSettableValues();

    return array_combine($allowed_view_modes, $allowed_view_modes);
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginViewModes(): array {
    // @todo Use computed property instead.
    if (($origin_view_modes = $this->getSetting('origin_view_modes')) && is_array($origin_view_modes)) {
      $entity_type_id = $this->getFieldDefinition()->getTargetEntityTypeId();

      $origin_view_modes = array_filter($origin_view_modes);
      $origin_view_modes = array_combine($origin_view_modes, $origin_view_modes);
      $origin_view_modes = array_intersect_key($origin_view_modes, $this->entityDisplayRepository()->getViewModeOptions($entity_type_id));

      return $origin_view_modes;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(AccountInterface $account = NULL): array {
    $entity_type_id = $this->getFieldDefinition()->getTargetEntityTypeId();
    $options = $this->entityDisplayRepository()->getViewModeOptions($entity_type_id);

    // Sort options by label (but ensure 'default' always comes first).
    uasort($options, function ($a, $b) {
      return strcasecmp($a, $b);
    });

    if (isset($options['default'])) {
      $default = $options['default'];
      unset($options['default']);
      $options = array_merge(['default' => $default], $options);
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleValues(AccountInterface $account = NULL): array {
    $values = array_keys($this->getPossibleOptions());

    // Sort values.
    sort($values);

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableOptions(AccountInterface $account = NULL, $allowed_only = TRUE): array {
    $options = $this->getPossibleOptions($account);

    // Remove 'default' view mode (if available)?
    if (isset($options['default'])) {
      unset($options['default']);
    }

    // Remove origin view modes configured for this field?
    if (($origin_view_modes = $this->getSetting('origin_view_modes')) && is_array($origin_view_modes)) {
      $options = array_diff_key($options, array_filter($origin_view_modes));
    }

    // Only return view modes allowed to switch to?
    if ($allowed_only && ($allowed_view_modes = $this->getSetting('allowed_view_modes')) && is_array($allowed_view_modes)) {
      $options = array_intersect_key($options, array_filter($allowed_view_modes) + ['' => '']);
    }

    // Sort options by label (but ensure 'default' always comes first).
    uasort($options, function ($a, $b) {
      return strcasecmp($a, $b);
    });

    if (isset($options['default'])) {
      $default = $options['default'];
      unset($options['default']);
      $options = array_merge(['default' => $default], $options);
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableValues(AccountInterface $account = NULL, $allowed_only = TRUE): array {
    $values = array_keys($this->getSettableOptions($account, $allowed_only));

    // Sort values.
    sort($values);

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewMode(): ?string {
    // @todo Use computed property instead.
    if (!$this->isEmpty()) {
      $entity_type_id = $this->getFieldDefinition()->getTargetEntityTypeId();
      $view_mode = [$this->value => $this->value];

      $view_mode_options = $this->entityDisplayRepository()->getViewModeOptions($entity_type_id);
      $allowed_view_modes = $this->getAllowedViewModes();

      $view_modes = array_intersect_key($view_mode, $view_mode_options, $allowed_view_modes);

      if ($view_modes) {
        return reset($view_modes);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable($view_mode): bool {
    // View mode switch field is only applicable if it is not empty, responsible
    // for given view mode and provides valid view mode value.
    if (!$this->isEmpty() && $this->isResponsible($view_mode)) {
      return (bool) $this->getViewMode();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    return !isset($this->value) || $this->value === NULL || $this->value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function isResponsible($view_mode): bool {
    $origin_view_modes = $this->getOriginViewModes();

    return isset($origin_view_modes[$view_mode]);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function onDependencyRemoval(FieldDefinitionInterface $field_definition, array $dependencies): bool {
    $changed = parent::onDependencyRemoval($field_definition, $dependencies);
    $view_modes_changed = FALSE;
    $setting_allowed_view_mode_setting = $field_definition->getSetting('allowed_view_modes');
    $view_modes = array_keys(array_filter(is_array($setting_allowed_view_mode_setting) ? $setting_allowed_view_mode_setting : []));

    // Update the allowed view mode setting if a view mode config dependency has
    // been removed.
    if (!empty($view_modes)) {
      $view_modes = array_combine($view_modes, $view_modes);

      // Prepare view mode IDs from view mode names.
      $view_mode_ids = array_map(function ($view_mode) use ($field_definition) {
        return implode('.', [
          $field_definition->getTargetEntityTypeId(),
          $view_mode,
        ]);
      }, $view_modes);

      // Load related view mode entities.
      /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_mode_entities */
      $view_mode_entities = \Drupal::entityTypeManager()
        ->getStorage('entity_view_mode')
        ->loadMultiple($view_mode_ids);

      /** @var \Drupal\view_mode_switch\ViewModeHelperInterface $view_mode_helper */
      $view_mode_helper = \Drupal::service('view_mode_switch.view_mode_helper');

      // Process allowed view mode dependencies.
      foreach ($view_mode_entities as $view_mode_entity) {
        if (isset($dependencies[$view_mode_entity->getConfigDependencyKey()][$view_mode_entity->getConfigDependencyName()])) {
          unset($view_modes[$view_mode_helper->getName($view_mode_entity)]);
          $view_modes_changed = TRUE;
        }
      }

      // In case we deleted the only view mode configured for the field we can
      // delete the fields as it gets obsolete with this.
      if ($view_modes === []) {
        $view_modes_changed = FALSE;
      }

      if ($view_modes_changed && $field_definition instanceof FieldConfigInterface) {
        $field_definition->setSetting('allowed_view_modes', $view_modes);
      }
    }

    $changed |= $view_modes_changed;

    return (bool) $changed;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties = [];

    // View mode name.
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('View mode'))
      ->setDescription(new TranslatableMarkup('The name of view mode to switch to.'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar_ascii',
          'length' => EntityTypeInterface::ID_MAX_LENGTH,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data): array {
    // Origin view modes.
    $element['origin_view_modes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('View modes to switch'),
      '#description' => $this->t('Select the origin view modes that should be switchable by this field.'),
      '#options' => $this->getPossibleOptions(),
      '#default_value' => $this->getSetting('origin_view_modes'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function storageSettingsToConfigData(array $settings): array {
    // Filter empty values from origin view modes.
    if (!empty($settings['origin_view_modes'])) {
      $settings['origin_view_modes'] = array_filter($settings['origin_view_modes']);
    }

    return $settings;
  }

}
