<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileLocator;

use InvalidArgumentException;

final class BundleMap
{
	/** @var array  */
	private $map;

	/**
	 * @param array $map
	 */
	public function __construct(array $map)
	{
		$this->map = array_map(static function (string $path) {
			return realpath($path);
		}, $map);
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isBundlePath(string $path): bool
	{
		return '@' === ($path[0] ?? NULL);
	}

	/**
	 * Returns an array [
	 *    0 => bundle name
	 *    1 => path
	 * ]
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function parseBundlePath(string $path): array
	{
		if ($this->isBundlePath($path)) {
			$path = substr($path, 1);
		}

		return (FALSE !== strpos($path, '/')) ? explode('/', $path, 2) : [$path, ''];
	}

	/**
	 * @param string $bundleName
	 *
	 * @return string
	 */
	public function getFixturesDirectory(string $bundleName): string
	{
		if (!isset($this->map[$bundleName])) {
			throw new InvalidArgumentException(sprintf(
				'An bundle "%s" isn\'t defined in the map.',
				$bundleName
			));
		}

		return $this->map[$bundleName];
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->map;
	}
}
