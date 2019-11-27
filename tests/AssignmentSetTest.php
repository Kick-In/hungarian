<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\AssignmentSet;
use PHPUnit\Framework\TestCase;

class AssignmentSetTest extends TestCase
{
	/** @var AssignmentSet */
	private $set;

	protected function setUp(): void
	{
		$this->set = new AssignmentSet();
	}

	public function testNormal()
	{
		$this->set->set(3, 5);
		self::assertEquals(5, $this->set->get(3));
		self::assertTrue($this->set->has(3));
		self::assertEquals(3, $this->set->getReverse(5));
		self::assertTrue($this->set->hasReverse(5));
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
			$this->set->get(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('forward', $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->set->getReverse(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('reverse', $e->getMessage());
		});
	}

	public function testRemove()
	{
		$this->set->set(3, 5);
		$this->set->remove(3);
		self::assertFalse($this->set->has(3));
		self::assertFalse($this->set->hasReverse(5));
	}

	public function testRemoveReverse()
	{
		$this->set->set(3, 5);
		$this->set->removeReverse(5);
		self::assertFalse($this->set->has(3));
		self::assertFalse($this->set->hasReverse(5));
	}

	public function testRemoveNonExistent()
	{
		TestUtil::assertThrows(function () {
			$this->set->remove(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('forward', $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->set->removeReverse(1);
		}, function (Exception $e) {
			$this->assertStringContainsStringIgnoringCase('reverse', $e->getMessage());
		});
	}
}
