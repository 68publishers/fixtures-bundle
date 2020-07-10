<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileLocator;

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

final class BundleFileLocator implements FileLocatorInterface
{
	/** @var \Nelmio\Alice\FileLocatorInterface  */
	private $fileLocator;

	/** @var \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap  */
	private $map;

	/**
	 * @param \Nelmio\Alice\FileLocatorInterface                         $fileLocator
	 * @param \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap $map
	 */
	public function __construct(FileLocatorInterface $fileLocator, BundleMap $map)
	{
		$this->fileLocator = $fileLocator;
		$this->map = $map;
	}

	/**
	 * {@inheritDoc}
	 */
	public function locate(string $name, string $currentPath = NULL): string
	{
		if ($this->map->isBundlePath($name)) {
			return $this->locateBundleFile($name);
		}

		return $this->fileLocator->locate($name, $currentPath);
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 * @throws \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
	 */
	private function locateBundleFile(string $name): string
	{
		[$bundleName, $path] = $this->map->parseBundlePath($name);
		$root = $this->map->getFixturesDirectory($bundleName);

		if (FALSE !== $path = realpath($root . DIRECTORY_SEPARATOR . $path)) {
			return $path;
		}

		throw new FileNotFoundException(sprintf(
			'Unable to find file or directory "%s".',
			$name
		));
	}
}
