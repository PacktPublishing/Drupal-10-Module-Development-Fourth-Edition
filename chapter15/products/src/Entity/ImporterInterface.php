<?php

namespace Drupal\products\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Importer configuration entity.
 */
interface ImporterInterface extends ConfigEntityInterface {

  /**
   * Returns the configuration specific to the chosen plugin.
   *
   * @return array
   *   The plugin configuration.
   */
  public function getPluginConfiguration();

  /**
   * Sets the plugin configuration.
   *
   * @param array $configuration
   *   The plugin configuration.
   */
  public function setPluginConfiguration(array $configuration);

  /**
   * Returns the Importer plugin ID to be used by this importer.
   *
   * @return string
   *   The plugin ID.
   */
  public function getPluginId();

  /**
   * Whether to update existing products if they have already been imported.
   *
   * @return bool
   *   Whether to update existing products.
   */
  public function updateExisting();

  /**
   * Returns the source of the products.
   *
   * @return string
   *   The source.
   */
  public function getSource();

  /**
   * Returns the Product type that needs to be created.
   *
   * @return string
   *   The product bundle to create.
   */
  public function getBundle();

}
