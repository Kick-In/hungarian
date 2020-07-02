<?php

namespace Kickin\Hungarian\Algo;


use Kickin\Hungarian\Matrix\Matrix;
use Kickin\Hungarian\Result\ResultSet;

class Hungarian extends AssignmentSolver
{
	/** @var int[][] */
	private $rowCache;

	private function getPrimeFromRow($row, $primed)
	{
		if (!array_key_exists($row, $primed)) {
			return false;
		}

		return $primed[$row];
	}

	private function getStarFromRow($row, $starred)
	{
		if (!array_key_exists($row, $starred)) {
			return false;
		}

		return $starred[$row];
	}

	private function getFirstUncoveredZero(Matrix $matrix, $coveredRow, $coveredColumn)
	{
		$non_covered_zero_matrix = [];
		for ($row = 0; $row < $matrix->getSize(); $row++) {
			if (in_array($row, $coveredRow, true)) continue;

			$cells = $this->getRow($matrix, $row);
			$zeroCells = array_keys($cells, 0, true);
			foreach ($zeroCells as $column) {
				if (!in_array($column, $coveredColumn, true)) {
					return [$row, $column];
				}
			}
		}

		return $non_covered_zero_matrix;
	}

	protected function solveMin(Matrix $matrix): ?ResultSet
	{
		$this->rowCache = [];
		$this->colReduce($matrix);
		$rri = $this->rowReduce($matrix);

		$cprimed = [];
		$cstarred = array_unique($rri);
		$coveredRow = [];
		$coveredColumn = array_values($cstarred);

		while (true) {
			/*
			 * Generate zero matrix
			 */
			$uncoveredZero = $this->getFirstUncoveredZero($matrix, $coveredRow, $coveredColumn);
			while ($uncoveredZero) {

				/*
				 * Step 1:
				 *  -  Select first non-covered zero and prime this selected zero
				 *  -  If has starred zero in row of selected zero
				 *     - Uncover column of starred zero
				 *     - Cover row of starred zero
				 *     Else
				 *     - Step 2
				 */
				[$row, $column] = $uncoveredZero;
				$cprimed[$row] = $column;
				if (array_key_exists($row, $cstarred)) {

					// get column from the starred zero in the row
					$column = $this->getStarFromRow($row, $cstarred);

					// uncover the column of the starred zero
					$key = array_search($column, $coveredColumn, true);
					unset($coveredColumn[$key]);

					// cover the row
					$coveredRow[] = $row;
				} else {

					/*
					 * Step 2:
					 *  -  Get the sequence of starred and primed zeros connecting to the initial primed zero
					 *     - Get the starred zero in the column of the primed zero
					 *     - Get the primed zero in the row of the starred zero
					 *  -  Unstar the starred zeros from the sequence
					 *  -  Star the primed zeros from the sequence
					 *  -  Empty the list with primed zeros
					 *  -  Empty the list with covered columns and covered rows
					 *  -  Cover the columns with a starred zero in it
					 */
					$starred = [];
					$primed = [];
					$primed[$row] = $column;
					$i = $row;
					while (true) {

						if (!in_array($primed[$i], $cstarred, true)) {

							// Unstar the starred zeros from the sequence
							foreach ($starred as $row => $column) {
								unset($cstarred[$row]);
							}

							// Star the primed zeros from the sequence
							foreach ($primed as $row => $column) {
								$cstarred[$row] = $column;
							}

							// Empty the list with primed zeros
							$cprimed = [];

							// Empty the list with covered columns
							$coveredColumn = [];

							// Empty the list with covered columns
							$coveredRow = [];

							// Cover the columns with a starred zero in it
							foreach ($cstarred as $row => $column) {
								$coveredColumn[] = $column;
							}
							break 1;
						}

						$star_row = array_search($primed[$i], $cstarred, true);
						$star_column = $primed[$i];
						$starred[$star_row] = $star_column;

						if (array_key_exists($star_row, $cprimed)) {
							$prime_row = $star_row;
							$prime_column = $this->getPrimeFromRow($prime_row, $cprimed);
							$primed[$prime_row] = $prime_column;
						} else {
							return null;
						}

						$i = $prime_row;
					}
				}

				$uncoveredZero = $this->getFirstUncoveredZero($matrix, $coveredRow, $coveredColumn);
			}

			/*
			 * Step 3:
			 *  -  If the number of covered columns is equal to the number of rows/columns of the cost matrix
			 *     - The currently starred zeros show the optimal solution
			 *
			 */
			if (count($coveredColumn) + count($coveredRow) === $matrix->getSize()) {
				$set = new ResultSet($matrix->getSize());
				foreach ($cstarred as $row => $col) {
					$set->set($row, $col);
				}

				return $set;
			}

			$non_covered_reduced_matrix = [];
			$once_covered_reduced_matrix = [];
			$twice_covered_reduced_matrix = [];
			for ($row = 0; $row < $matrix->getSize(); $row++) {
				$cells = $this->getRow($matrix, $row);
				foreach ($cells as $column => $cell) {
					if (!in_array($row, $coveredRow, true) && !in_array($column, $coveredColumn,
							true)) {
						$non_covered_reduced_matrix[$row][$column] = $cell;
					} elseif (in_array($row, $coveredRow, true) && in_array($column, $coveredColumn,
							true)) {
						$twice_covered_reduced_matrix[$row][$column] = $cell;
					} else {
						$once_covered_reduced_matrix[$row][$column] = $cell;
					}
				}
			}

			$min = $this->min($non_covered_reduced_matrix);

			foreach ($non_covered_reduced_matrix as $row => $cells) {
				foreach ($cells as $column => $cell) {
					$value = $matrix->get($row, $column);
					$matrix->set($row, $column, $value - $min);
					unset($this->rowCache[$row]);
				}
			}

			foreach ($twice_covered_reduced_matrix as $row => $cells) {
				foreach ($cells as $column => $cell) {
					$value = $matrix->get($row, $column);
					$matrix->set($row, $column, $value + $min);
					unset($this->rowCache[$row]);
				}
			}

		}
		return null;
	}

	private function getRow($matrix, $row)
	{
		if (array_key_exists($row, $this->rowCache)) {
			return $this->rowCache[$row];
		} else {
			$values = [];
			for ($i = 0; $i < $matrix->getSize(); $i++) {
				$values[$i] = $matrix->get($row, $i);
			}
			$this->rowCache[$row] = $values;
			return $values;
		}
	}
}
