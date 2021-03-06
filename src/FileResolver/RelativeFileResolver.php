<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\FileResolver;

use Fidry\AliceDataFixtures\FileResolverInterface;
use SixtyEightPublishers\FixturesBundle\FileExporterInterface;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

final class RelativeFileResolver implements FileResolverInterface, FileExporterInterface
{
	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/** @var \SixtyEightPublishers\FixturesBundle\FileExporterInterface  */
	private $fileExporter;

	/** @var \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap  */
	private $bundleMap;

	/** @var array  */
	private $fixtureDirs;

	/**
	 * @param \Fidry\AliceDataFixtures\FileResolverInterface             $fileResolver
	 * @param \SixtyEightPublishers\FixturesBundle\FileExporterInterface $fileExporter
	 * @param \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap $bundleMap
	 * @param array                                                      $fixtureDirs
	 */
	public function __construct(FileResolverInterface $fileResolver, FileExporterInterface $fileExporter, BundleMap $bundleMap, array $fixtureDirs)
	{
		$this->fileResolver = $fileResolver;
		$this->fileExporter = $fileExporter;
		$this->bundleMap = $bundleMap;

		$this->fixtureDirs = array_filter(array_map(static function (string $path) {
			return realpath($path);
		}, $fixtureDirs));

		usort($this->fixtureDirs, static function ($a, $b) {
			return strlen($b) <=> strlen($a);
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve(array $filePaths): array
	{
		return array_map(function (string $realPath) {
			return $this->getRelativePath($realPath);
		}, $this->fileResolver->resolve($filePaths));
	}

	/**
	 * {@inheritDoc}
	 */
	public function export(): array
	{
		return array_map(function (string $realPath) {
			return $this->getRelativePath($realPath);
		}, $this->fileExporter->export());
	}

	/**
	 * @param string $realPath
	 *
	 * @return string
	 * @throws \Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException
	 */
	private function getRelativePath(string $realPath): string
	{
		foreach ($this->bundleMap->toArray() as $bundleName => $root) {
			if (NULL !== ($relativePath = $this->tryParseRelativePath($realPath, $root))) {
				return '@' . $bundleName . DIRECTORY_SEPARATOR . $relativePath;
			}
		}

		foreach ($this->fixtureDirs as $root) {
			if (NULL !== ($relativePath = $this->tryParseRelativePath($realPath, $root))) {
				return $relativePath;
			}
		}

		throw new FileNotFoundException(sprintf(
			'Can\'t resolve relative path for file %s',
			$realPath
		));
	}

	/**
	 * @param string $realPath
	 * @param string $root
	 *
	 * @return string|NULL
	 */
	private function tryParseRelativePath(string $realPath, string $root): ?string
	{
		$root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if (0 !== strncmp($realPath, $root, $length = strlen($root))) {
			return NULL;
		}

		return substr($realPath, $length);
	}
}
