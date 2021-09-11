<?php

namespace Kickin\Hungarian\Result;


use Exception;
use Iterator;
use Kickin\Hungarian\Matrix\Matrix;
use Kickin\Hungarian\Util\Assertions;
use Kickin\Hungarian\Util\Marker;
use SplFixedArray;

class ResultSet implements Iterator
{
	public static function merge(ResultSet $r1, ResultSet $r2): ResultSet
	{
		$result = new ResultSet($r1->getSize() + $r2->getSize());
		foreach ($r1 as list($row, $col)) {
			$result->set($row, $col);
		}
		foreach ($r2 as list($row, $col)) {
			$result->set($row, $col);
		}
		return $result;
	}

	/** @var SplFixedArray */
	private $rowAssignments;
	/** @var Iterator */
	private $rowIterator;
	/** @var SplFixedArray */
	private $colAssignments;
	/** @var Iterator */
	private $colIterator;
	/** @var bool */
	private $labeled = false;

	public function __construct(int $size)
	{
		$this->reset($size);
	}

	private function findRow($row): void
	{
		$this->rewind();
		while ($this->rowIterator->valid() && $this->rowIterator->current() !== $row) {
			$this->next();
		}
	}

	private function findCol($col): void
	{
		$this->rewind();
		while ($this->colIterator->valid() && $this->colIterator->current() !== $col) {
			$this->next();
		}
	}

	public function set($row, $col): void
	{
		if ($row instanceof Marker || $row === NULL) {
			$row = NULL;
		} elseif ($this->hasRow($row)) {
			throw new Exception("Row is already assigned");
		}

		if ($col instanceof Marker || $col === NULL) {
			$col = NULL;
		} elseif ($this->hasCol($col)) {
			throw new Exception("Column is already assigned");
		}

		if ($row === NULL && $col === NULL) {
			throw new Exception("Cannot create an assignment with both row and column being NULL");
		}

		$this->rewind();
		while ($this->rowIterator->current() !== NULL || $this->colIterator->current() !== NULL) {
			$this->next();
			if (
			!$this->valid()
			) {
				throw new Exception("Cannot assign a new row or column, result set is exhausted");
			}
		}
		$idx = $this->rowIterator->key();
		$this->rowAssignments[$idx] = $row;
		$this->colAssignments[$idx] = $col;
	}

	public function removeRow($row): void
	{
		Assertions::assertNotNull($row);
		$this->findRow($row);
		if ($this->rowAssignments->valid()) {
			$idx = $this->rowAssignments->key();
			unset($this->rowAssignments[$idx]);
			unset($this->colAssignments[$idx]);
		} else {
			throw new Exception("Row is not defined");
		}
	}

	public function removeCol($col): void
	{
		Assertions::assertNotNull($col);
		$this->findCol($col);
		if ($this->colAssignments->valid()) {
			$idx = $this->colAssignments->key();
			unset($this->rowAssignments[$idx]);
			unset($this->colAssignments[$idx]);
		} else {
			throw new Exception("Column is not defined");
		}
	}

	public function getRow($row)
	{
		Assertions::assertNotNull($row);
		$this->findRow($row);
		if ($this->rowAssignments->valid()) {
			return $this->colAssignments->current();
		} else {
			throw new Exception("Row could not be found");
		}
	}

	public function getCol($col)
	{
		Assertions::assertNotNull($col);
		$this->findCol($col);
		if ($this->colAssignments->valid()) {
			return $this->rowAssignments->current();
		} else {
			throw new Exception("Column could not be found");
		}
	}

	public function hasRow($row): bool
	{
		Assertions::assertNotNull($row);
		$this->findRow($row);
		return $this->rowIterator->valid();
	}

	public function hasCol($col): bool
	{
		Assertions::assertNotNull($col);
		$this->findCol($col);
		return $this->colIterator->valid();
	}

	/**
	 * Resets a set to its initial state.
	 * Note that the size can be changed if desired.
	 * @param int $size
	 */
	public function reset(?int $size = null): void
	{
		if ($size === null) {
			$size = $this->rowAssignments->getSize();
		}
		$this->rowAssignments = new SplFixedArray($size);
		$this->colAssignments = new SplFixedArray($size);

		$this->rowIterator = method_exists($this->rowAssignments, 'getIterator') ?
				$this->rowAssignments->getIterator() :
				$this->rowAssignments;
		$this->colIterator = method_exists($this->colAssignments, 'getIterator') ?
				$this->colAssignments->getIterator() :
				$this->colAssignments;
	}

	/**
	 * Converts the labels from numeric indices to labeled ones
	 * @param array $rowLabels
	 * @param array $colLabels
	 * @throws Exception when the result set already has labels
	 */
	public function applyLabels(array $rowLabels, array $colLabels): void
	{
		if ($this->labeled) {
			throw new Exception("ResultSet is already labeled");
		}
		$this->labeled = true;

		$this->rewind();
		while ($this->rowIterator->valid()) {
			$newLabel = $rowLabels[$this->rowIterator->current()];
			$this->rowAssignments[$this->rowIterator->key()] = $newLabel;
			$this->rowIterator->next();
		}
		while ($this->colIterator->valid()) {
			$newLabel = $colLabels[$this->colIterator->current()];
			$this->colAssignments[$this->colIterator->key()] = $newLabel;
			$this->colIterator->next();
		}
	}

	/**
	 * Returns the size of the result set
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->rowAssignments->getSize();
	}

	/**
	 * Returns the assignment cost over a given matrix.
	 * @param Matrix $matrix
	 * @return int
	 * @throws Exception when the provided matrix and result set are not of the same size
	 */
	public function getCost(Matrix $matrix): int
	{
		Assertions::assertEqual($this->rowAssignments->getSize(), $matrix->getSize(), "Expected a matrix of a size equal to the result set");
		$cost = 0;

		foreach ($this as [$row, $col]) {
			if ($row !== NULL && $col !== NULL) {
				$cost += $matrix->get($row, $col);
			}
		}

		return $cost;
	}

	/**
	 * Returns a copy of this ResultSet without any Markers
	 * @return ResultSet
	 * @throws Exception
	 */
	public function withoutUnassigned(): ResultSet
	{
		$rows = [];
		$cols = [];
		foreach ($this as [$row, $col]) {
			if ($row !== NULL && !($row instanceof Marker) && $col !== NULL && !($col instanceof Marker)) {
				$rows[] = $row;
				$cols[] = $col;
			}
		}

		$set = new ResultSet(count($rows));
		for ($i = 0; $i < count($rows); $i++) {
			$set->set($rows[$i], $cols[$i]);
		}

		return $set;
	}

	/*
	 * The functions below implement the Iterator interface
	 */
	public function rewind(): void
	{
		$this->rowIterator->rewind();
		$this->colIterator->rewind();
	}

	public function next(): void
	{
		$this->rowIterator->next();
		$this->colIterator->next();
	}

	public function key()
	{
		return null;
	}

	public function current(): array
	{
		return [
			$this->rowIterator->current(),
			$this->colIterator->current(),
		];
	}

	public function valid(): bool
	{
		return (
			$this->rowIterator->valid() &&
			$this->colIterator->valid()
		);
	}
}
