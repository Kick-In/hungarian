<?php

namespace Kickin\Hungarian;


use Exception;
use OutOfBoundsException;

class Assertions
{
	/**
	 * Throws an exception when $check returns a falsy value
	 *
	 * @param callable $check
	 * @param $value
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertThat(callable $check, $value, string $message = null)
	{
		if (!$check($value)) {
			if ($message == null) {
				$message = "Expected value $value to pass check";
			}

			throw new Exception($message);
		}
	}

	/**
	 * Throws an exception when $length does not contain $expected items
	 *
	 * @param int $expected
	 * @param array $actual
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertLength(int $expected, array $actual, string $message = null)
	{
		$count = count($actual);
		if ($count != $expected) {
			if ($message == null) {
				$message = "Expected array to contain $expected items but found $count";
			}

			throw new Exception($message);
		}
	}

	/**
	 * Throws an exception when $actual is smaller than $min
	 *
	 * @param int $min
	 * @param int $actual
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertLargerEqual(int $min, int $actual, string $message = null)
	{
		if ($actual < $min) {
			if ($message == null) {
				$message = "Expected value to be larger than or equal to $min but was $actual";
			}

			throw new Exception($message);
		}
	}

	/**
	 * Throws an exception when $actual is not between bounds, $min inclusive.
	 * $actual satisfy $min<=$actual<$max
	 *
	 * @param int $min
	 * @param int $max
	 * @param int $actual
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertInBounds(int $min, int $max, int $actual, string $message = null)
	{
		if ($actual < $min || $actual >= $max) {
			if ($message == null) {
				$message = "Expected value to be between $min and $max but was $actual";
			}

			throw new OutOfBoundsException($message);
		}
	}
}
