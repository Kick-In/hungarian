<?php

namespace Kickin\Hungarian;

use Exception;

/**
 * Class Matrix
 * Models a square matrix where all values are positive.
 *
 * @package Kickin\Hungarian
 */
class Matrix
{
	/**
	 * @var int the size of this matrix
	 */
	private $size;

	/**
	 * $matrix[$col][$row]
	 * @var array
	 */
	private $matrix;

	/**
	 * Initializes a matrix of $size by $size.
	 * All values will be initialized as PHP_INT_MAX
	 * @param int $size
	 * @throws Exception when the provided $size is smaller than 1
	 */
	public function __construct(int $size)
	{
		Assertions::assertLargerEqual(1, $size,
			sprintf("Cannot initialize a matrix of size %s, expected a size to be 1 or larger", $size));

		$this->size = $size;
		$this->matrix = array_fill(0, $size, array_fill(0, $size, PHP_INT_MAX));
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	public function get(int $col, int $row): int
	{
		Assertions::assertInBounds(0, $this->size, $row,
			sprintf("Row %d does not exist on a matrix of size %d", $row, $this->size));
		Assertions::assertInBounds(0, $this->size, $col,
			sprintf("Column %d does not exist on a matrix of size %d", $col, $this->size));

		return $this->matrix[$col][$row];
	}

	public function set(int $col, int $row, int $value)
	{
		Assertions::assertInBounds(0, $this->size, $row,
			sprintf("Row %d does not exist on a matrix of size %d", $row, $this->size));
		Assertions::assertInBounds(0, $this->size, $col,
			sprintf("Column %d does not exist on a matrix of size %d", $col, $this->size));
		Assertions::assertLargerEqual(0, $value);

		$this->matrix[$col][$row] = $value;
	}

	public function getRow(int $row)
	{
		Assertions::assertInBounds(0, $this->size, $row, "Expected row to be in range");
		return array_map(function ($col) use ($row) {
			return $col[$row];
		}, $this->matrix);
	}

	public function setRow(int $row, array $values)
	{
		Assertions::assertLength($this->size, $values);
		for ($col = 0; $col < $this->size; $col++) {
			$value = $values[$col];
			Assertions::assertThat('is_int', $value, "Expected value at index $col to be an integer");
			$this->set($col, $row, $value);
		}
	}

	public function getCol(int $col)
	{
		Assertions::assertInBounds(0, $this->size, $col, "Expected column to be in range");
		return $this->matrix[$col];
	}

	public function setCol(int $col, array $values)
	{
		Assertions::assertLength($this->size, $values);
		for ($row = 0; $row < $this->size; $row++) {
			$value = $values[$row];
			Assertions::assertThat('is_int', $value, "Expected value at index $row to be an integer");
			$this->set($col, $row, $value);
		}
	}
}
