<?php
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Mon 26 Mar 2012 02:12:57 PM VLAST $
 */

class DbDate extends DateTime {
  private $_format = 'Y-m-d';
  public function __toString() {
    return (string)parent::format($this->_format);
  }
}
