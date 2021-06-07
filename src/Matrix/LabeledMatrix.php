<?php

namespace Kickin\Hungarian\Matrix;


use Exception;
use Kickin\Hungarian\Util\Assertions;
use SplObjectStorage;

class LabeledMatrix extends Matrix
{
	/** @var SplObjectStorage */
	private $rowLabels;
	/** @var SplObjectStorage */
	private $colLabels;

	/**
	 * Constructs a labeled matrix.
	 * Note that this matrix does not have to be square, contrary to its parent class
	 * @param object[] $rowLabels
	 * @param object[] $colLabels
	 * @throws Exception when either $rowLabels or $colLabels is empty
	 */
	public function __construct(array $rowLabels, array $colLabels)
	{
		Assertions::assertLargerEqual(1, count($rowLabels), "Expected at least 1 row");
		Assertions::assertLargerEqual(1, count($colLabels), "Expected at least 1 column");
		Assertions::assertArraySequential($rowLabels, 'Expected row labels to be sequential');
		Assertions::assertArraySequential($colLabels, 'Expected column labels to be sequential');
		$size = max(count($rowLabels), count($colLabels));
		parent::__construct($size);

		$this->rowLabels = new SplObjectStorage();
		foreach ($rowLabels as $i => $label) {
			$this->rowLabels[$label] = $i;
		}
		$this->colLabels = new SplObjectStorage();
		foreach ($colLabels as $i => $label) {
			$this->colLabels[$label] = $i;
		}
	}

	public function getRowLabels(): array
	{
		return $this->getLabels($this->rowLabels);
	}

	public function getColLabels(): array
	{
		return $this->getLabels($this->colLabels);
	}

	protected function getLabels(SplObjectStorage $labels): array
	{
		$result = array_fill(0, $this->getSize(), null);
		$labels->rewind();
		while ($labels->valid()) {
			$result[$labels->getInfo()] = $labels->current();
			$labels->next();
		}
		return $result;
	}

	/**
	 * Gets a value from the matrix.  $row and $col can be either a label or an integer
	 * @param mixed $row
	 * @param mixed $col
	 * @return int
	 */
	public function get($row, $col): int
	{
		$row = $this->resolve($row, $this->rowLabels);
		$col = $this->resolve($col, $this->colLabels);
		return parent::get($row, $col);
	}

	/**
	 * Sets a value in the matrix. $row and $col can be either a label or an integer
	 * @param mixed $row
	 * @param mixed $col
	 * @param int $value
	 * @throws Exception
	 */
	public function set($row, $col, int $value): void
	{
		$row = $this->resolve($row, $this->rowLabels);
		$col = $this->resolve($col, $this->colLabels);
		parent::set($row, $col, $value);
	}

	/**
	 * Returns the index of a given object
	 * @param mixed $key
	 * @param SplObjectStorage $labels
	 * @return int
	 * @throws Exception
	 */
	protected function resolve($key, SplObjectStorage $labels): int
	{
		if (is_int($key)) {
			return $key;
		} elseif (isset($labels[$key])) {
			return $labels[$key];
		} else {
			throw new Exception(sprintf("Cannot find label '%s'", $key));
		}
	}

	public function shuffle(): void
	{
		//Prepare parameters
		$copy = clone $this;
		$rows = range(0, $this->getSize() - 1);
		$cols = range(0, $this->getSize() - 1);
		shuffle($rows);
		shuffle($cols);

		//Shuffle the actual matrix
		for ($i = 0; $i < $this->getSize(); $i++) {
			for ($j = 0; $j < $this->getSize(); $j++) {
				$value = $copy->get($i, $j);
				$this->set($rows[$i], $cols[$j], $value);
			}
		}

		//Update the labels to match
		$this->rowLabels->rewind();
		while ($this->rowLabels->valid()) {
			$value = $this->rowLabels->getInfo();
			$this->rowLabels->setInfo($rows[$value]);
			$this->rowLabels->next();
		}
		$this->colLabels->rewind();
		while ($this->colLabels->valid()) {
			$value = $this->colLabels->getInfo();
			$this->colLabels->setInfo($cols[$value]);
			$this->colLabels->next();
		}
	}

	public function __clone()
	{
		parent::__clone();
		$this->rowLabels = clone $this->rowLabels;
		$this->colLabels = clone $this->colLabels;
	}
}
