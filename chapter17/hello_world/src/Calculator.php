<?php

namespace Drupal\hello_world;

/**
 * Class used to demonstrate a simple Unit test.
 */
class Calculator {

  /**
   * The first number.
   *
   * @var mixed
   */
  protected $a;

  /**
   * The second number.
   *
   * @var mixed
   */
  protected $b;

  /**
   * Constructs a Calculator.
   */
  public function __construct($a, $b) {
    $this->a = $a;
    $this->b = $b;
  }

  /**
   * Performs the addition.
   */
  public function add() {
    return $this->a + $this->b;
  }

  /**
   * Performs the subtraction.
   */
  public function subtract() {
    return $this->a - $this->b;
  }

  /**
   * Performs the multiplication.
   */
  public function multiply() {
    return $this->a * $this->b;
  }

  /**
   * Performs the division.
   */
  public function divide() {
    return $this->a / $this->b;
  }

}
