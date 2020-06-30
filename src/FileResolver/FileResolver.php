<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileResolver;

use Nette\Utils\Finder;
use Nelmio\Alice\FileLocatorInterface;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

final class FileResolver implements FileResolverInterface
{
	/** @var \Nelmio\Alice\FileLocatorInterface  */
	private $locator;

	/** @var array  */
	private $fixtureDirs;

	/**
	 * @param \Nelmio\Alice\FileLocatorInterface $locator
	 * @param array                              $fixtureDirs
	 */
	public function __construct(FileLocatorInterface $locator, array $fixtureDirs)
	{
		$this->locator = $locator;
		$this->fixtureDirs = $fixtureDirs;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(array $filePaths): array
	{
		$newPaths = [];

		foreach ($filePaths as $filePath) {
			if ('@' === $filePath[0]) {
				$newPaths[] = $this->getRealPaths($this->locator->locate($filePath));

				continue;
			}

			$newPaths[] = $this->getRealPathsFromFixtureDirs($filePath);
		}

		return array_values(array_unique(array_merge([], ... $newPaths)));
	}

	/**
	 * @param string $path
	 *
	 * @return array
	 * @throws \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
	 */
	private function getRealPathsFromFixtureDirs(string $path): array
	{
		$paths = $realPaths = [];

		foreach ($this->fixtureDirs as $fixtureDir) {
			$paths[] = $filePath = $fixtureDir . DIRECTORY_SEPARATOR . $path;

			try {
				$realPaths[] = $this->getRealPaths($this->locator->locate($filePath));
			} catch (FileNotFoundException $e) {
				# nothing
			}
		}

		if (0 >= count($realPaths) && 0 < count($paths)) {
			throw new FileNotFoundException(sprintf(
				'File or directory %s not found. All paths are invalid [%s].',
				$path,
				implode(', ', $paths)
			));
		}

		return array_merge([], ...$realPaths);
	}

	/**
	 * @param string $path
	 *
	 * @return array
	 * @throws \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
	 */
	private function getRealPaths(string $path): array
	{
		if (is_dir($path)) {
			return $this->getFilesFromDirectory($path);
		}

		if (file_exists($path)) {
			return [$path];
		}

		throw FileNotFoundException::createForNonExistentFile($path);
	}

	/**
	 * @param string $directory
	 *
	 * @return array
	 */
	private function getFilesFromDirectory(string $directory): array
	{
		return array_keys(iterator_to_array(Finder::findFiles('*.yaml', '*.yml', '*.php', '*.json')->in($directory)));
	}
}
