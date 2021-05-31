<?php

namespace Kickin\Hungarian\Matrix;


use Exception;
use Kickin\Hungarian\Util\StringContainer;
use SplObjectStorage;

class StringMatrix extends LabeledMatrix
{
	/**
	 * The StringMatrix behaves identical to a labeled matrix except that it allows strings for keys
	 * @param string[] $rowLabels
	 * @param string[] $colLabels
	 * @throws Exception when either $rowLabels or $colLabels is empty
	 */
	public function __construct(array $rowLabels, array $colLabels)
	{
		$rowLabels = array_map([StringContainer::class, 'forValue'], $rowLabels);
		$colLabels = array_map([StringContainer::class, 'forValue'], $colLabels);
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
