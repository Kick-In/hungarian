<?php

namespace Kickin\Hungarian\Matrix;


use Exception;
use Kickin\Hungarian\Util\Marker;
use Kickin\Hungarian\Util\StringContainer;
use SplObjectStorage;

class StringMatrix extends LabeledMatrix
{
	/**
	 * Maps a value to either a StringContainer or returns the value directly when it is a Marker.
	 * Other values are not allowed and will result in an error.
	 *
	 * @param $value
	 *
	 * @return Marker|StringContainer
	 * @throws Exception when the provided value is neither a string or a Marker
	 */
	private static function mapStringLabels($value)
	{
		if ($value instanceof Marker) {
			return $value;
		} elseif (is_string($value)) {
			return StringContainer::forValue($value);
		} else {
			throw new Exception("String matrix labels should either be strings or markers");
		}
	}

	/**
	 * The StringMatrix behaves identical to a labeled matrix except that it allows strings for keys
	 * @param string[] $rowLabels
	 * @param string[] $colLabels
	 * @throws Exception when either $rowLabels or $colLabels is empty
	 */
	public function __construct(array $rowLabels, array $colLabels)
	{
		$rowLabels = array_map([self::class, 'mapStringLabels'], $rowLabels);
		$colLabels = array_map([self::class, 'mapStringLabels'], $colLabels);
		parent::__construct($rowLabels, $colLabels);
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

	protected function getLabels(SplObjectStorage $labels): array
	{
		$array = parent::getLabels($labels);

		return array_map(static function ($label) {
			return ($label instanceof StringContainer)
				? $label->get()
				: $label;
		}, $array);
	}
}
