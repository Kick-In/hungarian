<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\Matrix\Matrix;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class MatrixTest extends TestCase
{
	private $matrix;

	protected function setUp(): void
	{
		$this->matrix = new Matrix(2);
	}

	public function testInitialValues()
	{
		for ($i = 0; $i < $this->matrix->getSize(); $i++) {
			for ($j = 0; $j < $this->matrix->getSize(); $j++) {
				$this->assertEquals(1e12, $this->matrix->get($i, $j), "Expected matrix to be initialized to 1e12");
			}
		}
	}

	public function testConstructZeroSized()
	{
		TestUtil::assertThrows(function () {
			new Matrix(0);
		}, function (Exception $exception) {
			$this->assertStringContainsString('non-positive', $exception->getMessage());
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

	public function testInvert()
	{
		$this->matrix->set(0, 0, 1);
		$this->matrix->set(0, 1, 0);
		$this->matrix->set(1, 0, 0);
		$this->matrix->set(1, 1, 1);
		$invert = $this->matrix->invert();
		$this->assertEquals(0, $invert->get(0, 0));
		$this->assertEquals(1, $invert->get(0, 1));
		$this->assertEquals(1, $invert->get(1, 0));
		$this->assertEquals(0, $invert->get(1, 1));
	}

	public function testClonability()
	{
		$this->matrix->set(0, 0, 1);
		$this->matrix->set(0, 1, 2);
		$this->matrix->set(1, 0, 3);
		$this->matrix->set(1, 1, 4);
		$copy = clone $this->matrix;
		$copy->set(0, 0, 5);
		$this->assertEquals(1, $this->matrix->get(0, 0));
		$this->assertEquals(5, $copy->get(0, 0));
	}
}
