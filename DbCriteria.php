<?
/**
 * @author Kirill "Nemoden" K 
 * $ Date: Fri 23 Mar 2012 04:36:40 PM VLAT $
 */

/**
 * basically needed to manage sql statmenet condiitons, havings, limit, etc.
 */
class DbCriteria {

  private $_bind_params = array();
  public $conditions = '';
  public $group_by = '';
  public $order_by = '';
  public $having = '';
  public $limit = '';
  public $offset = '';
  public $for_update = false;

  public function __construct($params=array()) {
    foreach ($params as $k => $v) {
      $this->$k = $v;
    }
  }

  public function bindParams($params=array()) {
    $this->_bind_params = array_merge($this->_bind_params, $params);
  }

  public function getBindParams() {
    return $this->_bind_params;
  }

  public function __toString() {
    return $this->getCriteria();
  }

  public function getCriteria() {
    $criteria=array();
    if (!empty($this->conditions)) {
      $criteria[] = sprintf('WHERE %s', $this->conditions);
    }
    if (!empty($this->group_by)) {
      $criteria[] = sprintf('GROUP BY %s', $this->group_by);
    }
    if (!empty($this->having)) {
      $criteria[] = sprintf('HAVING %s', $this->having);
    }
    if (!empty($this->order_by)) {
      $criteria[] = sprintf('ORDER BY %s', $this->order_by);
    }
    if (!empty($this->limit)) {
      $criteria[] = 'LIMIT';
      if (is_numeric($this->offset)) {
        $criteria[] = sprintf('%s OFFSET %s', $this->limit, $this->offset);
      }
      else {
        $criteria[] = sprintf('%s', $this->limit);
      }
    }
    if (!empty($this->for_update)) {
      $criteria[] = 'FOR UPDATE';
    }
    return implode(' ', $criteria);
  }

}
