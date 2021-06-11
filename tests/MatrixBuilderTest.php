<?php

namespace Kickin\Hungarian\Tests;


use Kickin\Hungarian\Matrix\MatrixBuilder;
use Kickin\Hungarian\Matrix\StringMatrix;
use PHPUnit\Framework\TestCase;
use stdClass;

class MatrixBuilderTest extends TestCase
{
	/** @var MatrixBuilder */
	private $builder;

	protected function setUp(): void
	{
		$this->builder = new MatrixBuilder();
		$this->builder->setRowSource([new stdClass(), new stdClass()]);
		$this->builder->setColSource([new stdClass(), new stdClass()]);
		$this->builder->setMappingFunction(function () {
			return null;
		});
	}

	public function testDefault(): void
	{
		$this->builder->setDefaultValue(5);
		$matrix = $this->builder->build();
		for ($i = 0; $i < $matrix->getSize(); $i++) {
			for ($j = 0; $j < $matrix->getSize(); $j++) {
				$this->assertEquals(5, $matrix->get($i, $j));
			}
		}
	}

	/**
	 * @author gronostajo (https://github.com/gronostajo)
	 */
	public function testAugmentString()
	{
		$this->builder->setRowSource(['A', 'B', 'C', 'D']);
		$this->builder->setColSource(['a', 'b']);
		$this->builder->setAugmentValue(3);
		$matrix = $this->builder->build();
		for ($i = 0; $i < $matrix->getSize(); $i++) {
			for ($j = 0; $j < $matrix->getSize(); $j++) {
				if ($j < 2) {
					$this->assertEquals(PHP_INT_MAX, $matrix->get($i, $j));
				} else {
					$this->assertEquals(3, $matrix->get($i, $j));
				}
			}
		}
	}

	public function testAugmentObject(): void
	{
		$this->builder->setRowSource([new stdClass(), new stdClass(), new stdClass(), new stdClass()]);
		$this->builder->setAugmentValue(3);
		$matrix = $this->builder->build();
		for ($i = 0; $i < $matrix->getSize(); $i++) {
			for ($j = 0; $j < $matrix->getSize(); $j++) {
				if ($j < 2) {
					$this->assertEquals(PHP_INT_MAX, $matrix->get($i, $j));
				} else {
					$this->assertEquals(3, $matrix->get($i, $j));
				}
			}
		}
	}

	public function testMapping(): void
	{
		$o1 = new stdClass();
		$o1->value = 1;
		$o2 = new stdClass();
		$o2->value = 2;
		$o3 = new stdClass();
		$o3->value = 3;
		$o4 = new stdClass();
		$o4->value = 4;

		$this->builder->setRowSource([$o1, $o2]);
		$this->builder->setColSource([$o3, $o4]);
		$this->builder->setMappingFunction(function ($a, $b) {
			return $a->value * $b->value;
		});
		$matrix = $this->builder->build();

		$this->assertEquals(3, $matrix->get($o1, $o3));
		$this->assertEquals(6, $matrix->get($o2, $o3));
		$this->assertEquals(4, $matrix->get($o1, $o4));
		$this->assertEquals(8, $matrix->get($o2, $o4));
	}

	public function testYieldsStringMatrix(): void
	{
		$builder = new MatrixBuilder();
		$builder->setRowSource(['a', 'b'])
				->setColSource(['c', 'd'])
				->setMappingFunction(function ($r, $c) {
					return 1;
				});

		$matrix = $builder->build();

		$this->assertInstanceOf(StringMatrix::class, $matrix);
		$this->assertEquals(['a', 'b'], $matrix->getRowLabels());
		$this->assertEquals(['c', 'd'], $matrix->getColLabels());
	}

	public function testSettingStringMatrix(): void
	{
		$builder = new MatrixBuilder();
		$builder->setRowSource(['a', 'b'])
				->setColSource(['c', 'd'])
				->setMappingFunction(function ($r, $c) {
					return 1;
				});

		$matrix = $builder->build();

		$matrix->set('a', 'c', 3);
		$this->assertEquals(3, $matrix->get('a', 'c'));
	}
}
