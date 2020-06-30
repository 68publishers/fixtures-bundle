<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileLocator;

use Nelmio\Alice\FileLocatorInterface;

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
			return $this->map->locate($name);
		}

		return $this->fileLocator->locate($name, $currentPath);
	}
}
