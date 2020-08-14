<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Helper;

use Nette\StaticClass;

final class ParametersHelper
{
	use StaticClass;

	/**
	 * @param array  $parameters
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function flatten(array $parameters, string $prefix = ''): array
	{
		return self::doFlatten($parameters, [], empty($prefix) ? '' : $prefix . '.');
	}

	/**
	 * @param array  $input
	 * @param array  $return
	 * @param string $prevKey
	 *
	 * @return array
	 */
	private static function doFlatten(array $input, array $return = [], string $prevKey = ''): array
	{
		foreach ($input as $key => $value) {
			$newKey = $prevKey . $key;

			if (is_array($value) && !in_array(key($value), [0, NULL], TRUE)) {
				$return[] = self::doFlatten($value, $return, $newKey . '.');

				continue;
			}

			$return[] = [$newKey => $value];
		}

		return array_merge([], ...$return);
	}
}
