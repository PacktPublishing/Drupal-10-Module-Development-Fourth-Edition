<?php

namespace Drupal\Tests\products\FunctionalJavascript;

use Drupal\file\FileInterface;
use Drupal\products\Entity\ImporterInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\products\Entity\ProductType;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\products\Entity\Importer;

/**
 * Tests the creation/edit of Importer entities using the CSV importer.
 *
 * @group products
 */
class ImporterFormTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'image',
    'file',
    'node',
    'products',
    'csv_importer_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The import file.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  /**
   * The user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    chmod('public://', 0777);
    $csv_path = \Drupal::service('extension.path.resolver')->getPath('module', 'csv_importer_test') . '/products.csv';
    $csv_contents = file_get_contents($csv_path);
    $this->file = \Drupal::service('file.repository')->writeData($csv_contents, 'public://simpletest-products.csv', FileSystemInterface::EXISTS_REPLACE);
    $this->admin = $this->drupalCreateUser(['administer site configuration']);
    ProductType::create(['id' => 'goods', 'label' => 'Goods'])->save();
  }

  /**
   * Tests the importer form.
   */
  public function testImporterForm() {
    $this->drupalGet('/admin/structure/importer/add');
    $assert = $this->assertSession();
    $assert->pageTextContains('Access denied');

    $this->drupalLogin($this->admin);
    $this->drupalGet('/admin/structure/importer/add');
    $assert->pageTextContains('Add importer');
    $assert->elementExists('css', '#edit-label');
    $assert->elementExists('css', '#edit-plugin');
    $assert->elementExists('css', '#edit-update-existing');
    $assert->elementExists('css', '#edit-source');
    $assert->elementExists('css', '#edit-bundle');
    $assert->elementNotExists('css', 'input[name="files[plugin_configuration_csv_file]"]');

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('plugin', 'csv');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'input[name="files[plugin_configuration_csv_file]"]');

    $page->fillField('label', 'Test CSV Importer');
    $this->assertJsCondition('jQuery(".machine-name-value").html() == "test_csv_importer"');
    $page->checkField('update_existing');
    $page->fillField('source', 'testing');
    $page->fillField('bundle', 'goods');
    $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri($this->file->getFileUri());
    $page->attachFileToField('files[plugin_configuration_csv_file]', $wrapper->realpath());
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('Save');
    $assert->pageTextContains('Created the Test CSV Importer Importer.');

    $config = Importer::load('test_csv_importer');
    $this->assertInstanceOf(ImporterInterface::class, $config);

    $fids = $config->getPluginConfiguration()['file'];
    $fid = reset($fids);
    $file = File::load($fid);
    $this->assertInstanceOf(FileInterface::class, $file);

    $this->drupalGet('admin/structure/importer/test_csv_importer/edit');
    $assert->pageTextContains('Edit Test CSV Importer');
    $assert->fieldValueEquals('label', 'Test CSV Importer');
    $assert->fieldValueEquals('plugin', 'csv');
    $assert->checkboxChecked('update_existing');
    $assert->fieldValueEquals('source', 'testing');
    $page->hasLink('products.csv');
    $assert->fieldValueEquals('bundle', 'Goods (goods)');
  }

}
