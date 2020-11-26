<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileResolver;

use Nette\Utils\Finder;
use Nelmio\Alice\FileLocatorInterface;
use Fidry\AliceDataFixtures\FileResolverInterface;
use SixtyEightPublishers\FixturesBundle\FileExporterInterface;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

final class FileResolver implements FileResolverInterface, FileExporterInterface
{
	/** @var \Nelmio\Alice\FileLocatorInterface  */
	private $locator;

	/** @var \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap  */
	private $bundleMap;

	/** @var array  */
	private $fixtureDirs;

	/**
	 * @param \Nelmio\Alice\FileLocatorInterface                         $locator
	 * @param \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap $bundleMap
	 * @param array                                                      $fixtureDirs
	 */
	public function __construct(FileLocatorInterface $locator, BundleMap $bundleMap, array $fixtureDirs)
	{
		$this->locator = $locator;
		$this->bundleMap = $bundleMap;
		$this->fixtureDirs = array_filter(array_map(static function (string $path) {
			return realpath($path);
		}, $fixtureDirs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(array $filePaths): array
	{
		$newPaths = [];

		foreach ($filePaths as $filePath) {
			if ($this->bundleMap->isBundlePath($filePath)) {
				$newPaths[] = $this->getRealPaths($this->locator->locate($filePath));

				continue;
			}

			$newPaths[] = $this->getRealPathsFromFixtureDirs($filePath);
		}

		return array_values(array_unique(array_merge([], ... $newPaths)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function export(): array
	{
		return $this->getFilesFromDirectories(...array_merge(
			array_values($this->fixtureDirs),
			array_values($this->bundleMap->toArray())
		));
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
			$paths[] = $fixtureDir . DIRECTORY_SEPARATOR . $path;

			try {
				$realPaths[] = $this->getRealPaths($this->locator->locate($path, $fixtureDir));
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
			return $this->getFilesFromDirectories($path);
		}

		if (file_exists($path)) {
			return [$path];
		}

		throw FileNotFoundException::createForNonExistentFile($path);
	}

	/**
	 * @param string ...$directories
	 *
	 * @return array
	 */
	private function getFilesFromDirectories(string ...$directories): array
	{
		return array_keys(iterator_to_array(Finder::findFiles('*.yaml', '*.yml', '*.php', '*.json', '*.neon')->from(...$directories)));
	}
}
