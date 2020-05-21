<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\Matrix\Matrix;
use Kickin\Hungarian\Result\ResultSet;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResultSetTest extends TestCase
{
	/** @var ResultSet */
	private $set;

	protected function setUp(): void
	{
		$this->set = new ResultSet(5);
	}

	public function testNormal()
	{
		$this->set->set(3, 5);
		self::assertEquals(5, $this->set->getRow(3));
		self::assertTrue($this->set->hasRow(3));
		self::assertEquals(3, $this->set->getCol(5));
		self::assertTrue($this->set->hasCol(5));
	}

	public function testInsertDuplicate()
	{
		$this->set->set(3, 5);
		TestUtil::assertThrows(function () {
			$this->set->set(3, 7);
		});
		TestUtil::assertThrows(function () {
			$this->set->set(7, 5);
		});
	}

	public function testGetNonExistent()
	{
		TestUtil::assertThrows(function () {
			$this->set->getRow(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('row', $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->set->getCol(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('column', $e->getMessage());
		});
	}

	public function testRemove()
	{
		$this->set->set(3, 5);
		$this->set->removeRow(3);
		self::assertFalse($this->set->hasRow(3));
		self::assertFalse($this->set->hasCol(5));
	}

	public function testRemoveReverse()
	{
		$this->set->set(3, 5);
		$this->set->removeCol(5);
		self::assertFalse($this->set->hasRow(3));
		self::assertFalse($this->set->hasCol(5));
	}

	public function testRemoveNonExistent()
	{
		TestUtil::assertThrows(function () {
			$this->set->removeRow(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('row', $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->set->removeCol(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('column', $e->getMessage());
		});
	}

	public function testSetExhaustion()
	{
		$set = new ResultSet(2);
		$set->set(1, 2);
		$set->set(3, 4);
		TestUtil::assertThrows(function () use ($set) {
			$set->set(5, 6);
		});
	}

	public function testApplyLabels()
	{
		$o1 = new stdClass();
		$o2 = new stdClass();
		$o3 = new stdClass();

		$set = new ResultSet(3);
		$set->set(0, 0);
		$set->set(2, 1);
		$set->set(1, 2);

		$set->applyLabels([$o1, $o2, $o3], [$o1, $o2, $o3]);
		$this->assertEquals($o1, $set->getRow($o1));
		$this->assertEquals($o3, $set->getRow($o2));
		$this->assertEquals($o2, $set->getRow($o3));
	}

	public function testGetCost1()
	{
		$set = new ResultSet(2);
		$set->set(0, 0);
		$set->set(1, 1);

		$matrix = new Matrix(2);
		$matrix->set(0, 0, 0);
		$matrix->set(0, 1, 1);
		$matrix->set(1, 0, 1);
		$matrix->set(1, 1, 0);

		self::assertEquals(0, $set->getCost($matrix));
	}

	public function testGetCost2()
	{
		$set = new ResultSet(3);
		$set->set(0, 0);
		$set->set(2, 1);
		$set->set(1, 2);

		$matrix = new Matrix(3);
		for ($i = 0; $i < 3; $i++) {
			for ($j = 0; $j < 3; $j++) {
				$matrix->set($i, $j, $i * 3 + $j);
			}
		}

		self::assertEquals(12, $set->getCost($matrix));
	}

	public function testGetCost3()
	{
		$set = new ResultSet(3);
		$set->set(0, 2);
		$set->set(2, 1);
		$set->set(1, 0);

		$matrix = new Matrix(3);
		for ($i = 0; $i < 3; $i++) {
			for ($j = 0; $j < 3; $j++) {
				$matrix->set($i, $j, $i * 3 + $j);
			}
		}

		self::assertEquals(12, $set->getCost($matrix));
	}

	public function testGetCost4()
	{
		$set = new ResultSet(5);
		$set->set(0, 3);
		$set->set(1, 0);
		$set->set(2, 4);
		$set->set(3, 2);
		$set->set(4, 1);


		$matrix = new Matrix(5);
		for ($i = 0; $i < 5; $i++) {
			for ($j = 0; $j < 5; $j++) {
				$matrix->set($i, $j, $i * 5 + $j);
			}
		}

		self::assertEquals(60, $set->getCost($matrix));
	}

	public function testMerge()
	{
		$set1 = new ResultSet(2);
		$set1->set("a", "b");
		$set1->set("b", "c");
		$set2 = new ResultSet(3);
		$set2->set("c", "d");
		$set2->set("d", "e");
		$set2->set("e", "f");

		$result = ResultSet::merge($set1, $set2);
		
		self::assertEquals(5, $result->getSize());
		self::assertEquals("b", $result->getRow("a"));
		self::assertEquals("c", $result->getRow("b"));
		self::assertEquals("d", $result->getRow("c"));
		self::assertEquals("e", $result->getRow("d"));
		self::assertEquals("f", $result->getRow("e"));
	}
}
