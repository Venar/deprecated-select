<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 4/13/15
 * Time: 8:42 PM
 */

namespace select;

require_once(dirname(__DIR__) . '/src/Select.php');
require_once(dirname(__DIR__) . '/src/Param.php');

class SelectSQLiteTest extends \PHPUnit_Framework_TestCase{
	/**
	 * @var \PDO
	 */
	static $sqliteConn;
	const TABLES_QUERY = "SELECT name FROM sqlite_master WHERE type='table'";
	
	public function setUp(){
		if (is_null(self::$sqliteConn)){
			$this->markTestSkipped('pdo_sqlite is not available');
		}
	}
	
	public static function setUpBeforeClass(){

		if (!extension_loaded('pdo_sqlite')){
			return;
		}
		
		self::$sqliteConn = new \PDO('sqlite::memory:');
		$result = self::$sqliteConn->exec("CREATE TABLE user(
		   ID INT PRIMARY KEY     NOT NULL,
		   NAME           TEXT    NOT NULL)");
		
//		$t = self::$sqliteConn->query(self::TABLES_QUERY);

//		$st = self::$sqliteConn->prepare(self::TABLES_QUERY);
//		$st->execute();
	//	var_dump($st->fetchAll(), "poo"); die();
	}
	
	public function testConstruct(){
		$select = new Select(self::TABLES_QUERY, 'SELECT', self::$sqliteConn);
		return $select;
	}

	public function testExecute(){
		$select = new Select(self::TABLES_QUERY, 'SELECT', self::$sqliteConn);
		$result = $select->execute();
		return $result;
	}

	public function testGetSingleItem(){
		$select = new Select(self::TABLES_QUERY, 'SELECT', self::$sqliteConn);
		$row = $select->getSingleItem();
		$this->assertNotNull($row);
		$this->assertNotEmpty($row);
		$this->assertArrayHasKey('name', $row);
		$this->assertEmpty($row['name'] == 'user');
	}

	public function testRowCount(){
		$select = new Select(self::TABLES_QUERY, 'SELECT', self::$sqliteConn);
		$this->assertEquals(1, $select->getRowCount(), 'count of rows');
	}
	
}