<?php
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Tue 27 Mar 2012 07:01:25 PM VLAST $
 */

class DbTable {
  protected $_schema;
  protected $_table;
  public function __construct($name, $schema='') {
    $this->_table = $name;
    $this->_schema = $schema;
  }
}
