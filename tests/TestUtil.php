<?php

namespace Kickin\Hungarian\Tests;


use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class TestUtil extends TestCase
{
	public static function assertThrows(callable $function, callable $exceptionCheck = NULL, string $message = 'Expected to catch an exception')
	{
		try {
			$function();
			TestCase::fail($message);
		} catch (AssertionFailedError $afe) {
			throw $afe;
		} catch (Exception $e) {
			if ($exceptionCheck === NULL) {
				TestCase::assertTrue(true);
			} else {
				$exceptionCheck($e);
			}
		}
	}
}
