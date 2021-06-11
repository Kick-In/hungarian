<?php

namespace Kickin\Hungarian\Matrix;


use Kickin\Hungarian\Util\Assertions;
use Kickin\Hungarian\Util\Marker;

/**
 * Helper class to build a labeled matrix.
 * Functions are not documented as they should be self-explanatory. (And totally not because I'm lazy.)
 * @package Kickin\Hungarian\Matrix
 */
class MatrixBuilder
{
	/** @var int */
	private $default;
	/** @var int */
	private $augment;
	/** @var object[] */
	private $rowSource;
	/** @var object[] */
	private $colSource;
	/** @var callable */
	private $mapper;

	public function __construct()
	{
		$this->reset();
	}

	public function setDefaultValue(int $value): self
	{
		$this->default = $value;
		return $this;
	}

	public function setAugmentValue(int $value): self
	{
		$this->augment = $value;
		return $this;
	}

	public function setRowSource(array $rows): self
	{
		$this->rowSource = $rows;
		return $this;
	}

	public function setColSource(array $cols): self
	{
		$this->colSource = $cols;
		return $this;
	}

	public function setMappingFunction(callable $mapping): self
	{
		$this->mapper = $mapping;
		return $this;
	}

	public function reset(): void
	{
		$this->default = PHP_INT_MAX;
		$this->augment = 0;
		$this->rowSource = [];
		$this->colSource = [];
		$this->mapper = null;
	}

	public function build(): LabeledMatrix
	{
		Assertions::assertLargerEqual(1, count($this->rowSource), "Expected at least one row to be provided");
		Assertions::assertLargerEqual(1, count($this->colSource), "Expected at least one column to be provided");
		Assertions::assertNotNull($this->mapper, "Expected a mapping function to be provided");

		if (is_string($this->rowSource[0])) {
			// Assert all data is string-based, mixed type labels are not allowed
			Assertions::assertAll($this->rowSource, 'is_string', "Expected all row labels to be strings");
			Assertions::assertAll($this->colSource, 'is_string', "Expected all column labels to be strings");
			// Instantiate the matrix as a string matrix, this will automatically convert the strings to StringContainers
			$type = StringMatrix::class;
		} else {
			// Assert all items are objects, mixed types are still not allowed
			Assertions::assertAll($this->rowSource, 'is_object', "Expected all row labels to be strings");
			Assertions::assertAll($this->colSource, 'is_object', "Expected all column labels to be strings");
			$type = LabeledMatrix::class;
		}

		$size      = max(count($this->rowSource), count($this->colSource));
		$rowSource = $this->augmentToLength($this->rowSource, $size);
		$colSource = $this->augmentToLength($this->colSource, $size);

		$matrix = new $type($rowSource, $colSource);
		foreach ($rowSource as $row) {
			foreach ($colSource as $col) {
				if ($row instanceof Marker || $col instanceof Marker) {
					$value = $this->augment;
				} else {
					$value = ($this->mapper)($row, $col);
				}
				if ($value === null) {
					$value = $this->default;
				}
				$matrix->set($row, $col, $value);
			}
		}

		return $matrix;
	}

	private function augmentToLength(array $array, int $length): array
	{
		while (count($array) < $length) {
			$array[] = new Marker();
		}
		return $array;
	}
}
