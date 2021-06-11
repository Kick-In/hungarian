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
	/** @var SplFixedArray */
	private $colAssignments;
	/** @var bool */
	private $labeled = false;

	public function __construct(int $size)
	{
		$this->reset($size);
	}

	private function findRow($row): void
	{
		$this->rowAssignments->rewind();
		$this->colAssignments->rewind();
		while ($this->rowAssignments->valid() && $this->rowAssignments->current() !== $row) {
			$this->rowAssignments->next();
			$this->colAssignments->next();
		}
	}

	private function findCol($col): void
	{
		$this->rowAssignments->rewind();
		$this->colAssignments->rewind();
		while ($this->colAssignments->valid() && $this->colAssignments->current() !== $col) {
			$this->rowAssignments->next();
			$this->colAssignments->next();
		}
	}

	public function set($row, $col): void
	{
		if ($this->hasRow($row)) {
			throw new Exception("Row is already assigned");
		}
		if ($this->hasCol($col)) {
			throw new Exception("Column is already assigned");
		}
		$this->rowAssignments->rewind();
		$this->colAssignments->rewind();
		while ($this->rowAssignments->current() !== null || $this->colAssignments->current() !== null) {
			$this->rowAssignments->next();
			$this->colAssignments->next();
			if (
				!$this->rowAssignments->valid() ||
				!$this->colAssignments->valid()
			) {
				throw new Exception("Cannot assign a new row or column, result set is exhausted");
			}
		}
		$idx = $this->rowAssignments->key();
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
		return $this->rowAssignments->valid();
	}

	public function hasCol($col): bool
	{
		Assertions::assertNotNull($col);
		$this->findCol($col);
		return $this->colAssignments->valid();
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

		$this->rowAssignments->rewind();
		while ($this->rowAssignments->valid()) {
			$newLabel = $rowLabels[$this->rowAssignments->current()];
			$this->rowAssignments[$this->rowAssignments->key()] = $newLabel;
			$this->rowAssignments->next();
		}

		$this->colAssignments->rewind();
		while ($this->colAssignments->valid()) {
			$newLabel = $colLabels[$this->colAssignments->current()];
			$this->colAssignments[$this->colAssignments->key()] = $newLabel;
			$this->colAssignments->next();
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
		$this->rowAssignments->rewind();
		$this->colAssignments->rewind();
		while ($this->rowAssignments->valid() && $this->colAssignments->valid()) {
			$row = $this->rowAssignments->current();
			$col = $this->colAssignments->current();
			if ($row !== null && $col !== null) {
				$cost += $matrix->get($row, $col);
			}
			$this->rowAssignments->next();
			$this->colAssignments->next();
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
			if (!$row instanceof Marker && !$col instanceof Marker) {
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
		$this->rowAssignments->rewind();
		$this->colAssignments->rewind();
	}

	public function next(): void
	{
		$this->rowAssignments->next();
		$this->colAssignments->next();
	}

	public function key()
	{
		return null;
	}

	public function current(): array
	{
		return [
			$this->rowAssignments->current(),
			$this->colAssignments->current()
		];
	}

	public function valid(): bool
	{
		return (
			$this->rowAssignments->valid() &&
			$this->colAssignments->valid()
		);
	}
}
