<?php

namespace Kickin\Hungarian;


use Exception;

class LabeledMatrix extends Matrix
{
	/** @var string[] */
	private $colLabels;
	/** @var string[] */
	private $rowLabels;

	/**
	 * LabeledMatrix constructor.
	 * @param int $size
	 * @param string[] $colLabels
	 * @param string[] $rowLabels
	 * @throws Exception
	 */
	public function __construct(int $size, $colLabels, $rowLabels)
	{
		Assertions::assertLength($size, $rowLabels, "Expected $size row labels");
		Assertions::assertLength($size, $colLabels, "Expected $size column labels");

		parent::__construct($size);
		$this->colLabels = $colLabels;
		$this->rowLabels = $rowLabels;
	}

	/**
	 * @param string|int $index
	 * @param string[] $list
	 * @return int
	 * @throws Exception
	 */
	private static function findIn($index, array $list): int
	{
		if (!is_int($index)) {
			$resolved = array_search($index, $list);
			if ($resolved === FALSE) {
				throw new Exception("Could not find element '$index' in list [" . join(', ', $list) . "]");
			}
			return $resolved;
		}
		return $index;
	}

	/**
	 * @param int[] $items
	 * @param string[] $keyLookup
	 * @return int[]
	 * @throws Exception
	 */
	private static function dereferenceLabels(array $items, array $keyLookup): array
	{
		$return = [];
		foreach ($items as $key => $value) {
			$return[self::findIn($key, $keyLookup)] = $value;
		}
		return $return;
	}

	private static function applyLabels(array $items, array $keyLookup): array
	{
		$return = [];
		foreach ($items as $key => $value) {
			$return[$keyLookup[$key]] = $value;
		}
		return $return;
	}

	/**
	 * @param int|string $col
	 * @param int|string $row
	 * @return int
	 * @throws Exception
	 */
	public function get($col, $row): int
	{
		$col = LabeledMatrix::findIn($col, $this->colLabels);
		$row = LabeledMatrix::findIn($row, $this->rowLabels);
		return parent::get($col, $row);
	}

	/**
	 * @param int|string $col
	 * @param int|string $row
	 * @param int $value
	 * @throws Exception
	 */
	public function set($col, $row, int $value): void
	{
		$col = LabeledMatrix::findIn($col, $this->colLabels);
		$row = LabeledMatrix::findIn($row, $this->rowLabels);
		parent::set($col, $row, $value);
	}

	public function getRow($row): array
	{
		$row = LabeledMatrix::findIn($row, $this->rowLabels);
		return self::applyLabels(
			parent::getRow($row),
			$this->colLabels
		);
	}

	public function getCol($col): array
	{
		$col = LabeledMatrix::findIn($col, $this->colLabels);
		return self::applyLabels(
			parent::getCol($col),
			$this->rowLabels
		);
	}

	/**
	 * @param int|string $row
	 * @param int[] $values
	 * @throws Exception
	 */
	public function setRow($row, array $values): void
	{
		$row = LabeledMatrix::findIn($row, $this->rowLabels);
		$values = self::dereferenceLabels($values, $this->colLabels);
		parent::setRow($row, $values);
	}

	/**
	 * @param int|string $col
	 * @param int[] $values
	 * @throws Exception
	 */
	public function setCol($col, array $values): void
	{
		$col = LabeledMatrix::findIn($col, $this->colLabels);
		$values = self::dereferenceLabels($values, $this->rowLabels);
		parent::setCol($col, $values);
	}

	/**
	 * Shuffles a matrix to remove the bias an algorithm might have
	 * @throws Exception
	 */
	public function shuffle(): void
	{
		$copy = clone $this;
		$newCols = $this->colLabels;
		$newRows = $this->rowLabels;
		shuffle($newCols);
		shuffle($newRows);

		for ($i = 0; $i < $this->getSize(); $i++) {
			$col = $newCols[$i];
			for ($j = 0; $j < $this->getSize(); $j++) {
				$row = $newRows[$j];
				$value = $copy->get($i, $j);
				$this->set($col, $row, $value);
			}
		}

		$this->colLabels = $newCols;
		$this->rowLabels = $newRows;
	}
}
