<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\LabeledMatrix;
use PHPUnit\Framework\TestCase;

class LabeledMatrixTest extends TestCase
{
	/** @var LabeledMatrix */
	private $matrix;

	protected function setUp(): void
	{
		$this->matrix = new LabeledMatrix(2,
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
			self::assertStringContainsString("a, b", $e->getMessage());
		});
		TestUtil::assertThrows(function () {
			$this->matrix->get('a', 'e');
		}, function (Exception $e) {
			self::assertStringContainsString("'e'", $e->getMessage());
			self::assertStringContainsString("c, d", $e->getMessage());
		});
	}

	public function testGetSetIntIndex()
	{
		$this->matrix->set(0, 0, 7);
		self::assertEquals(7, $this->matrix->get(0, 0));
	}

	public function testSetRow()
	{
		$this->matrix->setRow('c', [
			'a' => 3,
			'b' => 4
		]);
		self::assertEquals(3, $this->matrix->get('a', 'c'));
		self::assertEquals(4, $this->matrix->get('b', 'c'));
	}

	public function testSetCol()
	{
		$this->matrix->setCol('a', [
			'c' => 3,
			'd' => 4
		]);
		self::assertEquals(3, $this->matrix->get('a', 'c'));
		self::assertEquals(4, $this->matrix->get('a', 'd'));
	}

	public function testGetCol()
	{
		$this->matrix->set('a', 'c', 3);
		$this->matrix->set('a', 'd', 4);
		self::assertEquals(
			[
				'c' => 3,
				'd' => 4
			],
			$this->matrix->getCol('a')
		);
	}

	public function testGetRow()
	{
		$this->matrix->set('a', 'c', 3);
		$this->matrix->set('b', 'c', 4);
		self::assertEquals(
			[
				'a' => 3,
				'b' => 4
			],
			$this->matrix->getRow('c')
		);
	}

	/*
	 * Matrix#shuffle is a probabilistic function.
	 * If there is a Heisenbug, this is a likely source.
	 */
	public function testShuffle()
	{
		$this->matrix->set('a', 'c', 1);
		$this->matrix->set('a', 'd', 2);
		$this->matrix->set('b', 'c', 3);
		$this->matrix->set('b', 'd', 4);

		$this->matrix->shuffle();

		self::assertEquals(1, $this->matrix->get('a', 'c'));
		self::assertEquals(2, $this->matrix->get('a', 'd'));
		self::assertEquals(3, $this->matrix->get('b', 'c'));
		self::assertEquals(4, $this->matrix->get('b', 'd'));
	}
}
