<?php
/*
 * This file is part of John Koniges' Select class
 * https://github.com/Venar/select
 *
 * Copyright (c) 2015 John J. Koniges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace select;

class SelectTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers Select::newSelect
	 * @uses   Select::__construct
	 * @uses   Select::getQuery
	 */
	public function testConstructorTableNameSelect() {
		$select = new Select('TableName');

		$assertSql = 'SELECT * FROM TableName';

		// Assert
		$this->assertEquals($select->getQuery(), $assertSql);
	}
}
