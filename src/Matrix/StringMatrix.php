<?php

namespace Kickin\Hungarian\Matrix;


use Exception;
use Kickin\Hungarian\Util\StringContainer;
use SplObjectStorage;

class StringMatrix extends LabeledMatrix
{
	private static $markerPrefix;
	private static $markerCounter;

	/**
	 * The StringMatrix behaves identical to a labeled matrix except that it allows only strings for keys
	 * @param string[] $rowLabels
	 * @param string[] $colLabels
	 * @throws Exception when either $rowLabels or $colLabels is empty
	 */
	public function __construct($rowLabels, $colLabels)
	{
		$rowLabels = array_map([StringContainer::class, 'forValue'], $rowLabels);
		$colLabels = array_map([StringContainer::class, 'forValue'], $colLabels);
		parent::__construct($rowLabels, $colLabels);
	}

	public static function getMarker()
	{
		return self::generateStringMarker();
	}

	public static function isMarker($value): bool
	{
		return self::$markerPrefix && strpos($value, self::$markerPrefix) === 0;
	}

	/**
	 * @inheritDoc
	 */
	protected function resolve($key, SplObjectStorage $labels): int
	{
		if (is_string($key)) {
			$key = StringContainer::forValue($key);
		}
		return parent::resolve($key, $labels);
	}

	protected function getLabels(SplObjectStorage $labels)
	{
		$array = parent::getLabels($labels);

		return array_map(function ($label) {
			if ($label instanceof StringContainer) {
				return $label->get();
			} else {
				return $label;
			}
		}, $array);
	}

	private static function generateStringMarker(): string
	{
		if (!self::$markerPrefix) {
			self::$markerPrefix = 'marker_' . self::generateRandomString(8) . '_';
		}

		return self::$markerPrefix . self::$markerCounter++;
	}

	private static function generateRandomString(int $charsLength): string
	{
		// Base64 uses 4 characters per 3 bytes. With non-multiples of 3 round up to have enough data.
		$bytesLength = ceil($charsLength * 3 / 4);
		try {
			$randomBytes = random_bytes($bytesLength);
		} catch (Exception $e) {  // fast secure randomness not supported, fallback to slower method
			$randomBytes = '';
			for ($i = 0; $i < $bytesLength; $i++) {
				$randomBytes .= chr(mt_rand(0, 255));
			}
		}

		$encodedBytes = base64_encode($randomBytes);

		return substr($encodedBytes, 0, $charsLength);  // leave out any extra characters or base64 padding
	}
}
