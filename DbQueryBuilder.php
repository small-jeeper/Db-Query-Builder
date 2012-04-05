<?php
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Tue 03 Apr 2012 04:00:39 PM VLAST $
 */

require_once 'DbExpression.php';

class DbQueryBuilder {

  private $_bind_params = array();
  private $_stmt;
  private $_type;
  protected $_stmt_arrangement = array(
    'insert' => array('insert','on_duplicate_key_update'),
    'update' => array('update','criteria'),
    'select' => array('select','criteria'),
    'delete' => array('delete','criteria'),
  ); 

  /**
   * adds backetics to a string
   */
  private function addBackticks($s) {
    if (FALSE === (strpos($s,'`'))) {
      return '`'.$s.'`';
    }
    return $s;
  }

  /**
   * adds backticks to table name
   * considers table can be either schema.table or just table alone
   */
  private function normalizeTable($table_name) {
    $t = explode('.', $table_name);
    if (sizeof($t)==2) {
      list($schema, $table) = $t; 
    }
    else {
      $schema = NULL;
      $table = $table_name;
    }

    if (isset($schema)) {
      return implode('.', array_map(array($this, 'addBackticks'), array($schema, $table)));
    }
    return $this->addBackticks($table);
  }

  /**
   * build insert statement
   */
  public function insert($table, $columns) {
    $this->reset();
    $this->_type= 'insert';
    $template = 'INSERT INTO %s (%s) VALUES (%s)';
    $table = $this->normalizeTable($table);
    $keys = $values = array();
    list($keys, $values) = $this->prepareColumns($columns);
    $this->_stmt['insert'] = sprintf($template, $table, implode(', ', $keys), implode(', ', $values));
    return $this;
  }

  /**
   * append on duplicate key update to insert statement
   */
  public function onDuplicate($columns) {
    $template = 'ON DUPLICATE KEY UPDATE %s';
    $keys = $values = array();
    list($keys, $values) = $this->prepareColumns($columns, '_dku');
    #k = :value
    $set_str = implode(', ', array_map(create_function('$k,$v', 'return $k." = ".$v;'), $keys, $values));
    $this->_stmt['on_duplicate_key_update'] = sprintf($template, $set_str);
    return $this;
  }

  protected function prepareColumns($columns, $placeholder_suffix='') {
    foreach ($columns as $key => $param) {
      $keys[] = sprintf('`%s`', $key);
      if ($param instanceof DbExpression) {
        $values[] = $param;
      }
      else {
        # key = :param_dku
        $placeholder = sprintf(':%s'.$placeholder_suffix, $key);
        $values[] = sprintf(':%s'.$placeholder_suffix, $key);
        $this->_bind_params[$placeholder] = $param;
      }
    }
    return array($keys, $values);
  }


  public function __toString() {
    return $this->getQuery();
  }

  public function getQuery() {
    $stmt = '';
    $i=0;
    foreach ($this->_stmt_arrangement[$this->_type] as $s) {
      $i++;
      if (isset($this->_stmt[$s])) {
        $stmt .= ($i==1?'':' ').$this->_stmt[$s];
      }
    }
    return $stmt;
  }

  protected function reset() {
    $this->_bind_params = array();
    $this->_stmt=NULL;
    $this->_type=NULL;
  }

  /**
   * build update statmenet
   */
  public function update($table, $columns, $criteria=NULL) {
    $this->_type = 'update';
    $template = 'UPDATE %s SET %s';
    $table = $this->normalizeTable($table);
    list($keys, $values) = $this->prepareColumns($columns);
    $set_str = implode(', ',array_map(create_function('$k,$v', 'return $k." = ".$v;'), $keys, $values));
    $this->_stmt['update'] = sprintf($template, $table, $set_str);
    if (isset($criteria)) {
      $this->addCriteria($criteria);
    }
    return $this;
  }

  public function select($table, $columns='*', $criteria=NULL) {
    $this->_type = 'select';
    $table = $this->normalizeTable($table);
    $template = 'SELECT %s FROM %s';
    if (is_array($columns)) {
      $columns = implode(', ', $columns);
    }
    $this->_stmt['select'] = sprintf($template, $columns, $table);
    if (isset($criteria)) {
      $this->addCriteria($criteria);
    }
    return $this;
  }

  public function delete($table, $criteria=NULL) {
    $this->_type = 'delete';
    $table = $this->normalizeTable($table);
    $template = 'DELETE FROM %s';
    $this->_stmt['delete'] = sprintf($template, $table);
    if (isset($criteria)) {
      $this->addCriteria($criteria);
    }
    return $this;
  }

  /**
   * @TODO: we should be able to append criteria on top of other
   */
  public function addCriteria($criteria) {
    if (is_array($criteria)) {
      $criteria = new DbCriteria($criteria);
    }
    $this->_bind_params = array_merge($this->_bind_params, $criteria->getBindParams());
    $this->_stmt['criteria'] = $criteria;
  }

  public function getBindParams() {
    return $this->_bind_params;
  }

  public function bindParams($params=array()) {
    $this->_bind_params = array_merge($this->_bind_params, $params);
    return $this;
  }

}
