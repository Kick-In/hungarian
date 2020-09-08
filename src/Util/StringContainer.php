<?php

namespace Kickin\Hungarian\Util;


class StringContainer
{
	private static $lookup = [];

	/**
	 * Resolves a string to an object.
	 * Operation is idempotent.
	 * @param string $value
	 * @return object
	 */
	public static function forValue(string $value)
	{
		if (array_key_exists($value, StringContainer::$lookup)) {
			return StringContainer::$lookup[$value];
		} else {
			return new StringContainer($value);
		}
	}

	/** @var StringContainer */
	private $value;

	private function __construct(string $value)
	{
		$this->value = $value;
		StringContainer::$lookup[$value] = $this;
	}

	public function get()
	{
		return $this->value;
	}

	public function __toString()
	{
		return $this->value;
	}
}
