<?php

namespace Kickin\Hungarian;

use Exception;

class AssignmentSet
{
	/**
	 * @var int[]
	 */
	private $fwdLookup;

	/**
	 * @var int[]
	 */
	private $revLookup;

	public function __construct()
	{
		$this->clear();
	}

	/**
	 * @param int $fwd
	 * @param int $rev
	 * @throws Exception
	 */
	public function set(int $fwd, int $rev): void
	{
		self::assertNotIn($fwd, $this->fwdLookup, "Forward element already in use");
		self::assertNotIn($rev, $this->revLookup, "Reverse element already in use");
		$this->fwdLookup[$fwd] = $rev;
		$this->revLookup[$rev] = $fwd;
	}

	/**
	 * @param int $fwd
	 * @return int
	 * @throws Exception
	 */
	public function get(int $fwd): int
	{
		self::assertIn($fwd, $this->fwdLookup, "Forward element does not exist");
		return $this->fwdLookup[$fwd];
	}

	/**
	 * @param int $fwd
	 * @return bool
	 */
	public function has(int $fwd)
	{
		return array_key_exists($fwd, $this->fwdLookup);
	}

	/**
	 * @param int $rev
	 * @return bool
	 */
	public function hasReverse(int $rev)
	{
		return array_key_exists($rev, $this->revLookup);
	}

	/**
	 * @param int $fwd
	 * @throws Exception
	 */
	public function remove(int $fwd): void
	{
		$rev = $this->get($fwd);
		$this->unset($fwd, $rev);
	}

	/**
	 * @param int $rev
	 * @return int
	 * @throws Exception
	 */
	public function getReverse(int $rev): int
	{
		self::assertIn($rev, $this->revLookup, "Reverse element does not exist");
		return $this->revLookup[$rev];
	}

	/**
	 * @param int $rev
	 * @throws Exception
	 */
	public function removeReverse(int $rev): void
	{
		$fwd = $this->getReverse($rev);
		$this->unset($fwd, $rev);
	}

	/**
	 * Resets a set to its initial state.
	 */
	public function clear(): void
	{
		$this->fwdLookup = [];
		$this->revLookup = [];
	}

	/**
	 * Removes a forward-reverse pair, note that it does not check if $fwd and $rev are associated
	 * @param int $fwd
	 * @param int $rev
	 * @throws Exception
	 */
	private function unset(int $fwd, int $rev): void
	{
		self::assertIn($fwd, $this->fwdLookup, "Forward element does not exist");
		self::assertIn($rev, $this->revLookup, "Reverse element does not exist");
		unset($this->fwdLookup[$fwd]);
		unset($this->revLookup[$rev]);
	}

	/**
	 * Throws an exception when $elem is not in $values
	 * @param int $elem
	 * @param array $values
	 * @param string $message
	 * @throws Exception
	 */
	private static function assertIn(int $elem, array $values, string $message): void
	{
		if (!array_key_exists($elem, $values)) {
			throw new Exception($message);
		}
	}

	/**
	 * Throws an exception when $elem is in $values
	 * @param int $elem
	 * @param array $values
	 * @param string $message
	 * @throws Exception
	 */
	private static function assertNotIn(int $elem, array $values, string $message): void
	{
		if (array_key_exists($elem, $values)) {
			throw new Exception($message);
		}
	}
}
