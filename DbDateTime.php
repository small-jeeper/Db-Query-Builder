<?php
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Thu 22 Mar 2012 07:52:09 PM VLAT $
 */

class DbDateTime extends DateTime {
  private $_format = 'Y-m-d H:i:s';
  public function __toString() {
    return (string)parent::format($this->_format);
  }
}
