<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\Matrix\LabeledMatrix;
use Kickin\Hungarian\Matrix\StringMatrix;
use PHPUnit\Framework\TestCase;

class LabeledMatrixTest extends TestCase
{
	/** @var LabeledMatrix */
	private $matrix;

	protected function setUp(): void
	{
		$this->matrix = new StringMatrix(
			['a', 'b'],
			['c', 'd']
		);
	}

	public function testGetSet()
	{
		$this->matrix->set('a', 'c', 2);
		$this->matrix->set('b', 'd', 4);
		self::assertEquals(2, $this->matrix->get('a', 'c'));
		self::assertEquals(4, $this->matrix->get('b', 'd'));
	}

	public function testGetNonExistant()
	{
		TestUtil::assertThrows(function () {
			$this->matrix->get('e', 'c');
		}, function (Exception $e) {
			self::assertStringContainsString("'e'", $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->matrix->get('a', 'e');
		}, function (Exception $e) {
			self::assertStringContainsString("'e'", $e->getMessage());
		});
	}

	public function testGetSetIntIndex()
	{
		$this->matrix->set(0, 0, 7);
		self::assertEquals(7, $this->matrix->get(0, 0));
	}

	/*
	 * Matrix#shuffle is a probabilistic function.
	 * If there is a Heisenbug, this is a likely source.
	 */
	public function testShuffle()
	{
		$matrix = new StringMatrix(
			['a', 'b', 'c'],
			['d', 'e', 'f']
		);

		$matrix->set('a', 'd', 1);
		$matrix->set('a', 'e', 2);
		$matrix->set('a', 'f', 3);
		$matrix->set('b', 'd', 4);
		$matrix->set('b', 'e', 5);
		$matrix->set('b', 'f', 6);
		$matrix->set('c', 'd', 7);
		$matrix->set('c', 'e', 8);
		$matrix->set('c', 'f', 9);

		$matrix->shuffle();

		self::assertEquals(1, $matrix->get('a', 'd'));
		self::assertEquals(2, $matrix->get('a', 'e'));
		self::assertEquals(3, $matrix->get('a', 'f'));
		self::assertEquals(4, $matrix->get('b', 'd'));
		self::assertEquals(5, $matrix->get('b', 'e'));
		self::assertEquals(6, $matrix->get('b', 'f'));
		self::assertEquals(7, $matrix->get('c', 'd'));
		self::assertEquals(8, $matrix->get('c', 'e'));
		self::assertEquals(9, $matrix->get('c', 'f'));
	}
}
