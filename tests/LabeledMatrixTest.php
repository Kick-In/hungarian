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
