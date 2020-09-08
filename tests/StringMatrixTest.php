<?php

namespace Kickin\Hungarian\Tests;


use Kickin\Hungarian\Matrix\StringMatrix;
use PHPUnit\Framework\TestCase;

class StringMatrixTest extends TestCase
{
	/** @var StringMatrix */
	private $matrix;

	protected function setUp(): void
	{
		$this->matrix = new StringMatrix(
				['a', 'b'],
				['c', 'd']
		);
	}

	public function testGetRowsColsReturnStrings()
	{
		$this->assertEquals(['a', 'b'], $this->matrix->getRowLabels(), "getRowLabels should return an array of strings");
		$this->assertEquals(['c', 'd'], $this->matrix->getColLabels(), "getColLabels should return an array of strings");
	}
}
