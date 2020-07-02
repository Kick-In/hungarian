<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use Kickin\Hungarian\Algo\AssignmentSolver;
use Kickin\Hungarian\Algo\Hungarian;
use Kickin\Hungarian\Matrix\LabeledMatrix;
use Kickin\Hungarian\Matrix\Matrix;
use PHPUnit\Framework\TestCase;
use stdClass;

class AssignmentSolverTest extends TestCase
{
	const SIZE = 5;

	/**
	 * @return AssignmentSolver[][]
	 */
	public function instances()
	{
		return [
			[new Hungarian()],
		];
	}

	/**
	 * @dataProvider instances
	 * @param AssignmentSolver $instance
	 * @throws Exception
	 */
	public function testSolveDiagonal(AssignmentSolver $instance)
	{
		$matrix = new Matrix(self::SIZE);
		for ($i = 0; $i < self::SIZE; $i++) {
			$matrix->set($i, $i, 0);
		}

		$result = $instance->solve($matrix);

		for ($i = 0; $i < self::SIZE; $i++) {
			self::assertEquals($i, $result->getRow($i));
		}
	}

	/**
	 * @dataProvider instances
	 * @param AssignmentSolver $instance
	 * @throws Exception
	 */
	public function testSolveInverseDiagonal(AssignmentSolver $instance)
	{
		$matrix = new Matrix(self::SIZE);
		for ($i = 0; $i < self::SIZE; $i++) {
			$matrix->set($i, self::SIZE - $i - 1, 0);
		}

		$result = $instance->solve($matrix);

		for ($i = 0; $i < self::SIZE; $i++) {
			self::assertEquals(self::SIZE - $i - 1, $result->getRow($i));
		}
	}

	/**
	 * @dataProvider instances
	 * @param AssignmentSolver $instance
	 * @throws Exception
	 */
	public function testSolveComplex(AssignmentSolver $instance)
	{
		$matrix = new Matrix(5);
		$matrix->set(0, 0, 0);
		$matrix->set(0, 1, 1);
		$matrix->set(0, 2, 2);
		$matrix->set(0, 3, 3);
		$matrix->set(0, 4, 4);
		$matrix->set(1, 0, 3);
		$matrix->set(1, 1, 2);
		$matrix->set(1, 2, 1);
		$matrix->set(1, 3, 0);
		$matrix->set(1, 4, 1);
		$matrix->set(2, 0, 0);
		$matrix->set(2, 1, 1);
		$matrix->set(2, 2, 2);
		$matrix->set(2, 3, 3);
		$matrix->set(2, 4, 4);
		$matrix->set(3, 0, 1);
		$matrix->set(3, 1, 2);
		$matrix->set(3, 2, 0);
		$matrix->set(3, 3, 1);
		$matrix->set(3, 4, 2);
		$matrix->set(4, 0, 1);
		$matrix->set(4, 1, 0);
		$matrix->set(4, 2, 3);
		$matrix->set(4, 3, 1);
		$matrix->set(4, 4, 2);

		$result = $instance->solve($matrix);

		self::assertEquals(3, $result->getCost($matrix));

		self::assertEquals(0, $result->getCol(1));
		self::assertEquals(1, $result->getCol(4));
		self::assertEquals(2, $result->getCol(0));
		self::assertEquals(3, $result->getCol(2));
		self::assertEquals(4, $result->getCol(3));
	}

	/**
	 * @dataProvider instances
	 * @param AssignmentSolver $solver
	 * @throws Exception
	 */
	public function testSolveTShaped(AssignmentSolver $solver)
	{
		$matrix = new Matrix(5);
		$matrix->set(0, 2, 0);
		$matrix->set(1, 3, 0);
		$matrix->set(2, 4, 0);
		$matrix->set(3, 1, 0);
		$matrix->set(4, 0, 0);

		$result = $solver->solve($matrix);

		self::assertEquals(2, $result->getRow(0));
		self::assertEquals(3, $result->getRow(1));
		self::assertEquals(4, $result->getRow(2));
		self::assertEquals(1, $result->getRow(3));
		self::assertEquals(0, $result->getRow(4));
	}

	/**
	 * @dataProvider instances
	 * @param AssignmentSolver $solver
	 * @throws Exception
	 */
	public function testSolveLabeled(AssignmentSolver $solver)
	{
		$rows = [new stdClass(), new stdClass(), new stdClass()];
		$cols = [new stdClass(), new stdClass(), new stdClass()];
		$matrix = new LabeledMatrix($rows, $cols);
		$matrix->set($rows[0], $cols[1], 0);
		$matrix->set($rows[1], $cols[2], 0);
		$matrix->set($rows[2], $cols[0], 0);

		$result = $solver->solve($matrix);

		self::assertEquals($cols[1], $result->getRow($rows[0]));
		self::assertEquals($cols[2], $result->getRow($rows[1]));
		self::assertEquals($cols[0], $result->getRow($rows[2]));
	}

	public function testEquivalence()
	{
		$matrix = new Matrix(9);
		for ($i = 0; $i < 9; $i++) {
			for ($j = 0; $j < 9; $j++) {
				$matrix->set($i, $j, rand(0, 10));
			}
		}

		$baseCost = $this->instances()[0][0]->solve($matrix)->getCost($matrix);
		foreach ($this->instances() as $instance) {
			$cost = $instance[0]->solve($matrix)->getCost($matrix);
			self::assertEquals($baseCost, $cost, "Expected different solvers to yield equivalent results");
		}
	}
}
