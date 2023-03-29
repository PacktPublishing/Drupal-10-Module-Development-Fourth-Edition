<?php

namespace Drupal\hello_world;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Lazy builder for the Hello World salutation.
 */
class HelloWorldLazyBuilder implements TrustedCallbackInterface {

  use StringTranslationTrait;

  /**
   * The salutation service.
   *
   * @var \Drupal\hello_world\HelloWorldSalutation
   */
  protected $salutation;

  /**
   * HelloWorldLazyBuilder constructor.
   *
   * @param \Drupal\hello_world\HelloWorldSalutation $salutation
   *   The salutation service.
   */
  public function __construct(HelloWorldSalutation $salutation) {
    $this->salutation = $salutation;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['renderSalutation'];
  }

  /**
   * Renders the Hello World salutation message.
   */
  public function renderSalutation() {
    $build = [];

    $build[] = [
      '#theme' => 'container',
      '#children' => [
        '#markup' => $this->salutation->getSalutation(),
      ]
    ];

    $url = Url::fromRoute('hello_world.hide_block');
    $url->setOption('attributes', ['class' => 'use-ajax']);
    $build[] = [
      '#type' => 'link',
      '#url' => $url,
      '#title' => $this->t('Remove'),
    ];

    return $build;
  }

}
