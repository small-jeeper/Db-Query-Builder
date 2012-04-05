<?php
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Fri 23 Mar 2012 10:56:32 AM VLAT $
 */

class DbExpression {
  private $_expression;
  public function __construct($expression) {
    $this->_expression = $expression;
  }
  public function __toString() {
    return (string)$this->_expression;
  }
}
