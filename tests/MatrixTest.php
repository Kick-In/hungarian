<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\Matrix;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class MatrixTest extends TestCase
{
	private $matrix;

	protected function setUp(): void
	{
		$this->matrix = new Matrix(2);
	}

	public function testConstructZeroSized()
	{
		TestUtil::assertThrows(function () {
			new Matrix(0);
		}, function (Exception $exception) {
			$this->assertStringContainsString('0', $exception->getMessage());
		});
	}

	public function testConstruct()
	{
		$matrix = new Matrix(3);
		$this->assertEquals(3, $matrix->getSize());
	}

	public function testSetGet()
	{
		$value = random_int(0, 1000);
		$this->matrix->set(0, 1, $value);
		$this->assertEquals($value, $this->matrix->get(0, 1));
	}

	public function testSetNegative()
	{
		TestUtil::assertThrows(function () {
			$this->matrix->set(0, 0, -1);
		});
	}

	public function testGetOOB()
	{
		TestUtil::assertThrows(function () {
			$this->matrix->get(-1, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->get(0, -1);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->get(3, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->get(0, 3);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
	}

	public function testSetOOB()
	{
		TestUtil::assertThrows(function () {
			$this->matrix->set(-1, 0, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->set(0, -1, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->set(3, 0, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->set(0, 3, 0);
		}, function (Exception $exception) {
			$this->assertInstanceOf(OutOfBoundsException::class, $exception);
		});
	}

	public function testSetRow()
	{
		$row = [0, 1];
		$this->matrix->setRow(1, $row);
		for ($i = 0; $i < $this->matrix->getSize(); $i++) {
			$this->assertEquals(
				$row[$i],
				$this->matrix->get($i, 1)
			);
		}
	}

	public function testSetCol()
	{
		$col = [0, 1];
		$this->matrix->setCol(1, $col);
		for ($i = 0; $i < $this->matrix->getSize(); $i++) {
			$this->assertEquals(
				$col[$i],
				$this->matrix->get(1, $i)
			);
		}
	}

	public function testGetCol()
	{
		$this->matrix->set(0, 0, 1);
		$this->matrix->set(0, 1, 2);
		$this->matrix->set(1, 0, 3);
		$this->matrix->set(1, 1, 4);

		self::assertEquals([1, 3], $this->matrix->getRow(0));
		self::assertEquals([2, 4], $this->matrix->getRow(1));

		TestUtil::assertThrows(function () {
			$this->matrix->getRow(-1);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->getRow(2);
		});
	}

	public function testGetRow()
	{
		$this->matrix->set(0, 0, 1);
		$this->matrix->set(0, 1, 2);
		$this->matrix->set(1, 0, 3);
		$this->matrix->set(1, 1, 4);

		self::assertEquals([1, 2], $this->matrix->getCol(0));
		self::assertEquals([3, 4], $this->matrix->getCol(1));

		TestUtil::assertThrows(function () {
			$this->matrix->getCol(-1);
		});
		TestUtil::assertThrows(function () {
			$this->matrix->getCol(2);
		});
	}

	public function testClonability()
	{
		$this->matrix->setRow(0, [1, 2]);
		$this->matrix->setRow(1, [3, 4]);
		$copy = clone $this->matrix;
		$copy->set(0, 0, 5);
		$this->assertEquals(1, $this->matrix->get(0, 0));
		$this->assertEquals(5, $copy->get(0, 0));
	}
}
