<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'view_mode_switch' widget.
 *
 * @FieldWidget(
 *   id = "view_mode_switch",
 *   label = @Translation("View mode switch"),
 *   field_types = {
 *     "view_mode_switch",
 *   },
 * )
 */
class ViewModeSwitchWidget extends WidgetBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // Inject current user.
    $instance->setCurrentUser($container->get('current_user'));

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $value = $items[$delta]->value ?? '';

    // Determine view modes allowed to switch to.
    $options_provider = $this->fieldDefinition
      ->getFieldStorageDefinition()
      ->getOptionsProvider('value', $items->getEntity());
    $options = $options_provider instanceof OptionsProviderInterface ? $options_provider->getSettableOptions($this->currentUser) : [];

    $is_required = $this->fieldDefinition->isRequired();

    if (!$is_required || $items[$delta]->isEmpty() || !isset($options[$value])) {
      $options = [
        '' => !$is_required ? $this->t('- No change -') : $this->t('- Select -'),
      ] + $options;
    }

    // View mode to switch to (if set).
    $element['value'] = $element + [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $value,
    ];

    return $element;
  }

  /**
   * Sets the current user.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   *
   * @return static
   *   The object itself for chaining.
   */
  public function setCurrentUser(AccountInterface $current_user): WidgetInterface {
    $this->currentUser = $current_user;

    return $this;
  }

}
