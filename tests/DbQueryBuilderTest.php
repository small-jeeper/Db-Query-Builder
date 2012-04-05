<?

require_once 'PHPUnit/Autoload.php';
require_once '../DbExpression.php';
require_once '../DbQueryBuilder.php';
require_once '../DbCriteria.php';
require_once '../DbDateTime.php';

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class DbQueryBuilderTest extends PHPUnit_Framework_TestCase {

  private $qb;

  /**
   * each time create new DbQueryBuilder
   */
  public function setUp() {
    $this->qb = new DbQueryBuilder();
  }

  public function testInsertQuery() {
    $params = array(
      'name' => 'Kirill',
      'date' => new DbExpression('NOW()'),
      'value' => 10,
    );
    $query = $this->qb->insert('test.test', $params);
    $this->assertEquals('INSERT INTO `test`.`test` (`name`, `date`, `value`) VALUES (:name, NOW(), :value)', (string)$query); 
    $this->assertEquals(array(':name' => 'Kirill', ':value' => '10'), $this->qb->getBindParams()); 
  }

  public function testInsertOrUpdateQuery() {

    $expected_stmt = 'INSERT INTO `test`.`test` (`name`, `date`, `value`) VALUES (:name, NOW(), :value) ON DUPLICATE KEY UPDATE `value` = value*3';

    $params = array(
      'name' => 'Kirill',
      'date' => new DbExpression('NOW()'),
      'value' => 10,
    );
    $update_params = array('value' => new DbExpression('value*3'));
    $query = $this->qb->insert('test.test', $params)->onDuplicate($update_params);
    $this->assertEquals($expected_stmt, (string)$query);
  }

  public function testUpdateQuery() {

    $expected_stmt = 'UPDATE `test` SET `name` = :name, `date` = :date';

    $date = new DbDateTime('NOW - 5 DAY');
    $update_params = array(
      'name' => 'Egor',
      'date' => $date,
    );
    $query = $this->qb->update('test', $update_params);
    $this->assertEquals($expected_stmt, (string)$query);
  }

  public function testUpdateQueryWithCriteria() {

    $expected_stmt = 'UPDATE `test`.`test` SET `name` = :name, `age` = :age WHERE id = :id'; 

    $criteria = new DbCriteria();
    $criteria->conditions = 'id = :id';
    $criteria->bindParams(array(
      ':id'=>1,
    ));
    $update_params = array(
      'name' => 'Kirill',
      'age' => 25,
    );
    $query = $this->qb->update('test.test', $update_params, $criteria);
    $this->assertEquals($expected_stmt, (string)$query);
    $this->assertEquals($this->qb->getBindParams(), array(':name' => 'Kirill', ':age' => 25, ':id' => 1));
  }

  public function testUpdateQueryWithArrayCriteria() {

    $expected_stmt = 'UPDATE `test`.`test` SET `name` = :name, `age` = :age WHERE id = :id';

    $update_params = array('name' => 'Kirill', 'age' => 25);
    $criteria = new DbCriteria(array('conditions' => 'id = :id'));
    $criteria->bindParams(array(':id' => 1));
    $query = $this->qb->update('test.test', $update_params, $criteria);
    $this->assertEquals($expected_stmt, (string)$query);
  }

  public function testSelectQuery() {

    $expected_stmt = 'SELECT * FROM `test`';

    $query = $this->qb->select('test');
    $this->assertEquals($expected_stmt, (string)$query);
  }

  public function testComplexSelectQuery() {

    $expected_stmt = 'SELECT count(*), date_format(birthday, "%d.%m.%Y") as `date` FROM `test`.`test` WHERE name = :name GROUP BY date(birthday) DESC LIMIT 10';

    $query = $this->qb->select('test.test', 'count(*), date_format(birthday, "%d.%m.%Y") as `date`', array(
      'conditions' => 'name = :name',
      'group_by' => 'date(birthday) DESC',
      'limit' => 10))->bindParams(array(':name' => 'Kirill')); 
    $this->assertEquals($expected_stmt, (string)$query);
    $this->assertEquals(array(':name' => 'Kirill'), $this->qb->getBindParams());
  }

  /*
  public function testSelectComplex() {
    $this->qb
      ->select('u.*')
      ->from('users', 'u')
      ->where(DbCondition::create(

        ));
  }


  class User extends Model {
    public function relations() {
      return array(
        'addresses' => array(
          self::HAS_MANY, backref('Phone', 'user_id') // external id is always last
        ), 
        'pets' => array(
          self::MANY_TO_MANY, backref('UserPets', $this->_pk, 'pet_id'), 
        ),

      );
    }
    $user->lazy('pets', array('alias' => 'p', 'criteria' => $petsCriteria,))->
           lazy('addresses', array('alias' => 'a', 'criteria' => $addressesCriteria,))->getAll()->cache()->fetch();

    $user->addPets(new Pet('laika'), new Pet('terrier'));
    $user->save();

    $pet = new Pet();
    $pet->findAllByBreed('laika');
    // how many users have laikas
    $user->lazy('pets', array('alias' => 'p','where' => 'p.breed = :breed'))->bindParam('breed' => 'laika')->count();

    #select * from users_pets up inner join pets p on p.user_id = where up.user_id = 1;
  }




  WHEN IT COMES TO CONSTRUCTING RELATIONS WITH PHP IT'S BLOWING YOUR MIND :-)
   */

}
