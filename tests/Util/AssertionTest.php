<?php

namespace Kickin\Hungarian\Tests\Util;


use Exception;
use Kickin\Hungarian\Tests\TestUtil;
use Kickin\Hungarian\Util\Assertions;
use PHPUnit\Framework\TestCase;

class AssertionTest extends TestCase
{
	public function testAssertArraySequentialPasses()
	{
		Assertions::assertArraySequential(['a', 'b', 'c']);
		$this->assertTrue(true, "Expected Assertions::assertArraySequential to not throw on a sequential array");
	}

	public function testAssertArraySequentialPassesEmpty()
	{
		Assertions::assertArraySequential([]);
		$this->assertTrue(true, "Expected Assertions::assertArraySequential to not throw on an empty array");
	}

	public function testAssertArraySequentialThrows()
	{
		TestUtil::assertThrows(function () {
			Assertions::assertArraySequential(['a' => 'a']);
		}, function (Exception $e) {
			$this->assertStringContainsString('associative array', $e->getMessage());
			$this->assertStringContainsString('sequential array', $e->getMessage());
		});
	}

	public function testAssertArraySequentialUsesMessage()
	{
		TestUtil::assertThrows(function () {
			Assertions::assertArraySequential(['a' => 'a'], 'Custom error message');
		}, function (Exception $e) {
			$this->assertEquals('Custom error message', $e->getMessage());
		});
	}
}
