<?php
/** 
 * @author Kirill "Nemoden" K 
 * $ Date : Wed 04 Apr 2012 02:42:22 PM VLAST $
 */

require_once 'PHPUnit/Autoload.php';
require_once '../DbCriteria.php';

class DbCriteriaTest extends PHPUnit_Framework_TestCase {

  public function testSetCriteriaWithConstructor() {
    $dbCriteria = new DbCriteria(array(
      'conditions' => 'name = :name',
      'group_by' => 'cnt',
      'having' => 'score = :score',
      'order_by' => 'name ASC',
      'limit' => '1',
      'offset' => '0',
      'for_update' => true,
    ));
    $dbCriteria->bindParams(array(':name' => 'John Doe', ':score' => 10));
    $expected_criteria = 'WHERE name = :name GROUP BY cnt HAVING score = :score ORDER BY name ASC LIMIT 1 OFFSET 0 FOR UPDATE';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $this->assertEquals(array(':name' => 'John Doe', ':score' => 10), $dbCriteria->getBindParams());
  }

  public function testWhereCondition() {
    $dbCriteria = new DbCriteria();
    $dbCriteria->conditions = 'name = :name';
    $expected_criteria = 'WHERE name = :name';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
  }

  public function testConstructCompositeCriteria() {
    $dbCriteria = new DbCriteria();
    $dbCriteria->conditions = 'name = :name AND surname = :surname';
    $expected_criteria = 'WHERE name = :name AND surname = :surname';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->conditions = 'name = :name';
    $dbCriteria->having = '`count` > 10';
    $expected_criteria = 'WHERE name = :name HAVING `count` > 10';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->order_by = '`count` DESC, name ASC';
    $expected_criteria = 'WHERE name = :name HAVING `count` > 10 ORDER BY `count` DESC, name ASC';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->group_by = '`count` DESC';
    $expected_criteria = 'WHERE name = :name GROUP BY `count` DESC HAVING `count` > 10 ORDER BY `count` DESC, name ASC';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->limit = '10';
    $expected_criteria = 'WHERE name = :name GROUP BY `count` DESC HAVING `count` > 10 ORDER BY `count` DESC, name ASC LIMIT 10';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->offset = '10';
    $expected_criteria = 'WHERE name = :name GROUP BY `count` DESC HAVING `count` > 10 ORDER BY `count` DESC, name ASC LIMIT 10 OFFSET 10';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
    $dbCriteria->for_update = true;
    $expected_criteria = 'WHERE name = :name GROUP BY `count` DESC HAVING `count` > 10 ORDER BY `count` DESC, name ASC LIMIT 10 OFFSET 10 FOR UPDATE';
    $this->assertEquals($expected_criteria, (string)$dbCriteria);
  }

}
