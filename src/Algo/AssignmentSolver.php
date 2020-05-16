<?php

namespace Kickin\Hungarian\Algo;

use Kickin\Hungarian\Matrix\LabeledMatrix;
use Kickin\Hungarian\Matrix\Matrix;
use Kickin\Hungarian\Result\ResultSet;
use Kickin\Hungarian\Util\Assertions;

abstract class AssignmentSolver
{
	protected abstract function solveMin(Matrix $matrix): ?ResultSet;

	public function solve(Matrix $matrix): ?ResultSet
	{
		$matrix = clone $matrix;
		$result = $this->solveMin($matrix);
		if ($matrix instanceof LabeledMatrix) {
			return $result->applyLabels(
				$matrix->getRowLabels(),
				$matrix->getColLabels()
			);
		}else {
			return $result;
		}
	}

	protected function rowReduce(Matrix $matrix): array
	{
		return $this->reduce(
			$matrix,
			function (int $index) use ($matrix) {
				$values = [];
				for ($i = 0; $i < $matrix->getSize(); $i++) {
					$values[$i] = $matrix->get($index, $i);
				}
				return $values;
			},
			function (int $index, array $values) use ($matrix) {
				for ($i = 0; $i < $matrix->getSize(); $i++) {
					$matrix->set($index, $i, $values[$i]);
				}
			}
		);
	}

	protected function colReduce(Matrix $matrix): array
	{
		return $this->reduce(
			$matrix,
			function (int $index) use ($matrix) {
				$values = [];
				for ($i = 0; $i < $matrix->getSize(); $i++) {
					$values[$i] = $matrix->get($i, $index);
				}
				return $values;
			},
			function (int $index, array $values) use ($matrix) {
				for ($i = 0; $i < $matrix->getSize(); $i++) {
					$matrix->set($i, $index, $values[$i]);
				}
			}
		);
	}

	protected function min(array $values): int
	{
		$min = PHP_INT_MAX;
		foreach ($values as $value) {
			if (is_array($value)) {
				$value = min($value);
			}
			Assertions::assertThat('is_int', $value, "Expected to only get integer-values in array");
			if ($value < $min) {
				$min = $value;
			}
		}
		return $min;
	}

	private function reduce(Matrix $matrix, callable $getter, callable $setter): array
	{
		$return = [];

		for ($i = 0; $i < $matrix->getSize(); $i++) {
			$vector = $getter($i);
			$min = PHP_INT_MAX;
			$minIndex = -1;

			//Find the minimum and save its index to $return
			for ($j = 0; $j < $matrix->getSize(); $j++) {
				$local = $vector[$j];
				if ($local < $min) {
					$minIndex = $j;
					$min = $local;
				}
			}
			$return[$i] = $minIndex;

			$reduced = array_map(function ($value) use ($min) {
				return $value - $min;
			}, $vector);
			$setter($i, $reduced);
		}

		return $return;
	}
}
