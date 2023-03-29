<?php

namespace Drupal\products\Commands;

use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\products\Plugin\ImporterPluginInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputOption;
use Drupal\products\Plugin\ImporterManager;

/**
 * Drush commands for products.
 */
class ProductCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * The importer manager.
   *
   * @var \Drupal\products\Plugin\ImporterManager
   */
  protected $importerManager;

  /**
   * The lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * ProductCommands constructor.
   *
   * @param \Drupal\products\Plugin\ImporterManager $importerManager
   *   The importer manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   */
  public function __construct(ImporterManager $importerManager, LockBackendInterface $lock) {
    $this->importerManager = $importerManager;
    $this->lock = $lock;
  }

  /**
   * Imports the Products.
   *
   * @param array $options
   *   The command options.
   *
   * @option importer
   *   The importer config ID to use.
   *
   * @command products-import-run
   * @aliases pir
   */
  public function import(array $options = ['importer' => InputOption::VALUE_OPTIONAL]) {
    $importer = $options['importer'];

    if (!is_null($importer)) {
      $plugin = $this->importerManager->createInstanceFromConfig($importer);
      if (is_null($plugin)) {
        $this->logger()->error(t('The specified importer does not exist.'));
        return;
      }

      $this->runPluginImport($plugin);
      return;
    }

    $plugins = $this->importerManager->createInstanceFromAllConfigs();
    if (!$plugins) {
      $this->logger()->error(t('There are no importers to run.'));
      return;
    }

    foreach ($plugins as $plugin) {
      $this->runPluginImport($plugin);
    }

  }

  /**
   * Runs the import function of a given plugin.
   *
   * @param \Drupal\products\Plugin\ImporterPluginInterface $plugin
   *   The importer plugin.
   */
  protected function runPluginImport(ImporterPluginInterface $plugin) {
    if (!$this->lock->acquire($plugin->getPluginId())) {
      $this->logger()->log('notice', t('The plugin @plugin is already running. Waiting for it to finish.', ['@plugin' => $plugin->getPluginDefinition()['label']]));
      if ($this->lock->wait($plugin->getPluginId())) {
        $this->logger()->log('notice', t('The wait is killing me. Giving up.'));
        return;
      }
    }

    $result = $plugin->import();
    $message_values = ['@importer' => $plugin->getImporterEntity()->label()];
    if ($result) {
      $this->logger()->notice(t('The "@importer" importer has been run.', $message_values));
      return;
    }

    $this->lock->release($plugin->getPluginId());
    $this->logger()->error(t('There was a problem running the "@importer" importer.', $message_values));
  }

}
