<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileLocator;

use InvalidArgumentException;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

final class BundleMap
{
	/** @var array  */
	private $map;

	/**
	 * @param array $map
	 */
	public function __construct(array $map)
	{
		$this->map = $map;
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
	 * @param string $name
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 * @throws \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
	 */
	public function locate(string $name): string
	{
		if (!$this->isBundlePath($name)) {
			throw new InvalidArgumentException('A path must start with @.');
		}

		$bundleName = substr($name, 1);
		[$bundleName, $path] = (FALSE !== strpos($bundleName, '/')) ? explode('/', $bundleName, 2) : [$bundleName, ''];

		if (!isset($this->map[$bundleName])) {
			throw new InvalidArgumentException(sprintf(
				'An bundle "%s" isn\'t defined in the map.',
				$bundleName
			));
		}

		if (FALSE !== $path = realpath($this->map[$bundleName] . '/' . $path)) {
			return $path;
		}

		throw new FileNotFoundException(sprintf(
			'Unable to find file or directory "%s".',
			$name
		));
	}
}
