<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario\Scene;

use Psr\Log\LoggerInterface;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectLoaderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;

class Scene implements SceneInterface
{
	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/** @var string  */
	private $name;

	/** @var string[]  */
	private $fixtures;

	/** @var \SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectLoaderInterface[] */
	private $objectLoaders;

	/**
	 * @param \Fidry\AliceDataFixtures\FileResolverInterface $fileResolver
	 * @param string                                         $name
	 * @param array                                          $fixtures
	 * @param array                                          $objectLoaders
	 */
	public function __construct(FileResolverInterface $fileResolver, string $name, array $fixtures, array $objectLoaders = [])
	{
		$this->fileResolver = $fileResolver;
		$this->name = $name;
		$this->fixtures = $fixtures;
		$this->objectLoaders = (static function (ObjectLoaderInterface ...$objectLoaders) {
			return $objectLoaders;
		})(...$objectLoaders);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFixtures(): array
	{
		return $this->fixtures;
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(DriverInterface $driver, LoggerInterface $logger, ?PurgeMode $purgeMode = NULL, array $parameters = []): void
	{
		$files = $this->fileResolver->resolve($this->fixtures);
		$objects = $this->loadObjects($driver, $logger);

		$logger->info('Loading files.', [
			'files' => $files,
		]);

		$driver->load($files, $parameters, $objects, $purgeMode);
		$driver->clear();

		$logger->info('Fixtures loaded.');
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 * @param \Psr\Log\LoggerInterface                                                             $logger
	 *
	 * @return object[]
	 */
	private function loadObjects(DriverInterface $driver, LoggerInterface $logger): array
	{
		if (0 >= count($this->objectLoaders)) {
			return [];
		}

		$logger->info('Loading objects from a storage.', [
			'entities' => array_values(array_unique(array_map(static function (ObjectLoaderInterface $objectLoader): string {
				return $objectLoader->getClassName();
			}, $this->objectLoaders))),
		]);

		$objects = [];

		foreach ($this->objectLoaders as $objectLoader) {
			$objects[] = $objectLoader->load($driver->getStorageDriver());
		}

		return array_merge([], ...$objects);
	}
}
