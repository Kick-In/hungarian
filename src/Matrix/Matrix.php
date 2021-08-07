<?php

namespace Kickin\Hungarian\Matrix;


use Exception;
use Kickin\Hungarian\Util\Assertions;
use OutOfBoundsException;
use RuntimeException;
use SplFixedArray;

class Matrix
{
	/** @var SplFixedArray */
	private $data;
	/** @var int */
	private $size;

	/**
	 * Creates a square matrix of positive or zero values.
	 * Initially, the matrix will only contain zeros
	 * @param int $size
	 * @throws Exception
	 */
	public function __construct(int $size)
	{
		Assertions::assertLargerEqual(1, $size, "Cannot create a matrix with non-positive size");
		$this->data = new SplFixedArray($size);
		$this->size = $size;

		for ($i = 0; $i < $size; $i++) {
			$this->data[$i] = new SplFixedArray($size);
			for ($j = 0; $j < $size; $j++) {
				$this->data[$i][$j] = 1e12;
			}
		}
	}

	/**
	 * Retrieves the size of the matrix.
	 * As this is a square matrix, there are as many rows as columns
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Gets a value from the matrix
	 * @param int $row
	 * @param int $col
	 * @return int
	 * @throws OutOfBoundsException when the row or column is out of bounds
	 */
	public function get($row, $col): int
	{
		try {
			return $this->data[$row][$col];
		} catch (RuntimeException $re) {
			throw new OutOfBoundsException("Index is out of bounds", $re->getCode(), $re);
		}
	}

	/**
	 * Sets a value in the matrix
	 * @param int $row
	 * @param int $col
	 * @param int $value
	 * @throws OutOfBoundsException when the row or column is out of bounds
	 * @throws Exception when $value is negative
	 */
	public function set($row, $col, int $value): void
	{
		Assertions::assertLargerEqual(0, $value, "Matrix can only contain positive integers");
		try {
			$this->data[$row][$col] = $value;
		} catch (RuntimeException $re) {
			throw new OutOfBoundsException("Index is out of bounds", $re->getCode(), $re);
		}
	}

	/**
	 * Takes all values in the matrix and maps them such that the largest value becomes 0 and vice-versa.
	 * While similarly named, this is NOT the same as mathematical inversion of a Matrix
	 *
	 * @param boolean $clone
	 *
	 * @return static
	 */
	public function invert($clone = true): self
	{
		$max = 0;

		for ($r = 0; $r < $this->size; $r++) {
			for ($c = 0; $c < $this->size; $c++) {
				$value = $this->get($r, $c);
				if ($value > $max) {
					$max = $value;
				}
			}
		}

		$new = $clone ? clone $this : $this;

		for ($r = 0; $r < $this->size; $r++) {
			for ($c = 0; $c < $this->size; $c++) {
				$new->set($r, $c, $max - $this->get($r, $c));
			}
		}

		return $new;
	}

	public function __clone()
	{
		$this->data = clone $this->data;
		for ($i = 0; $i < $this->getSize(); $i++) {
			$this->data[$i] = clone $this->data[$i];
		}
	}
}
