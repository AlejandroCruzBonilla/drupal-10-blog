<?php

namespace Drupal\view_mode_switch\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

/**
 * Defines an item list class for view mode switch fields.
 *
 * @see \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItem
 */
class ViewModeSwitchItemList extends FieldItemList implements ViewModeSwitchItemListInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getAllowedViewModes(): array {
    return $this->getRepresentativeItem()->getAllowedViewModes();
  }

  /**
   * Returns a representative view mode switch field item.
   *
   * If the field is not empty, the returned value the first field item.
   * Otherwise a dummy item created by createItem() method is returned.
   *
   * @return \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface
   *   The representative view mode switch field item.
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getRepresentativeItem(): ViewModeSwitchItemInterface {
    /** @var \Drupal\view_mode_switch\Plugin\Field\FieldType\ViewModeSwitchItemInterface $item */
    $item = $this->first() ?: $this->createItem();

    return $item;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getOriginViewModes(): array {
    return $this->getRepresentativeItem()->getOriginViewModes();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getViewMode(): ?string {
    return $this->getRepresentativeItem()->getViewMode();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isApplicable($view_mode): bool {
    return $this->getRepresentativeItem()->isApplicable($view_mode);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isResponsible($view_mode): bool {
    return $this->getRepresentativeItem()->isResponsible($view_mode);
  }

}
