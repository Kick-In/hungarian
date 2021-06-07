<?php

namespace Kickin\Hungarian\Util;


use Exception;
use OutOfBoundsException;

class Assertions
{
	/**
	 * Throws an exception when the provided value is null
	 * @param $value
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertNotNull($value, string $message = null)
	{
		if ($value === null) {
			if ($message === null) {
				$message = "Expected value to be a non-null value";
			}

			throw new Exception($message);
		}
	}

	/**
	 * Throws an exception when $value1 and $value2 are not equal
	 * @param mixed $value1
	 * @param mixed $value2
	 * @param string|null $message
	 * @throws Exception
	 */
	static function assertEqual($value1, $value2, string $message = null)
	{
		if ($value1 !== $value2) {
			if ($message === null) {
				$message = "Expected values to be equal";
			}

			throw new Exception($message);
		}
	}

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
			if ($message === null) {
				$message = "Expected value $value to pass check";
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
			if ($message === null) {
				$message = "Expected value to be larger than or equal to $min but was $actual";
			}

			throw new Exception($message);
		}
	}

	static function assertAll(iterable $data, callable $check, string $message = NULL)
	{
		foreach ($data as $idx => $item) {
			if (!$check($item)) {
				if ($message === NULL) {
					$message = "Expected all items to pass, but item at index $idx failed";
				}

				throw new Exception($message);
			}
		}
	}

	/**
	 * Asserts an array is sequential, or not associative.
	 * Acceptable arrays either have no explicitly defined keys, or only use sequential indices,
	 * like ['a', 'b', 'c'] or [0 => 'a', 1 => 'b', 2 => 'c']
	 *
	 * @param array       $array
	 * @param string|null $message
	 *
	 * @throws Exception
	 */
	static function assertArraySequential(array $array, string $message = NULL)
	{
		if ($array !== [] && array_keys($array) !== range(0, count($array) - 1)) {
			if ($message === NULL) {
				$message = 'Expected a sequential array, but got an associative array';
			}

			throw new Exception($message);
		}
	}
}
