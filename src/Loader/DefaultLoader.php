<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Fidry\AliceDataFixtures\FileResolverInterface;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface;
use SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectLoaderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;

final class DefaultLoader implements LoaderInterface
{
	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/** @var \Psr\Log\LoggerInterface  */
	private $logger;

	/** @var array  */
	private $parameters = [];

	/**
	 * @param \Fidry\AliceDataFixtures\FileResolverInterface $fileResolver
	 * @param \Psr\Log\LoggerInterface|NULL                  $logger
	 */
	public function __construct(FileResolverInterface $fileResolver, LoggerInterface $logger = NULL)
	{
		$this->fileResolver = $fileResolver;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * @param array $parameters
	 *
	 * @return void
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(DriverInterface $driver, ScenarioInterface $scenario): array
	{
		$files = $this->fileResolver->resolve($scenario->getFixtures());
		$objects = $this->loadObjects($driver, $scenario);

		$this->logger->info(sprintf(
			'Loading fixtures for scenario "%s"',
			$scenario->getName()
		), [
			'files' => $files,
		]);

		$fixtures = $driver->load($files, $this->parameters, $objects, $scenario->getPurgeMode() ? $scenario->getPurgeMode()->getMode() : NULL);

		$this->logger->info('fixtures loaded');

		return $fixtures;
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface                      $scenario
	 *
	 * @return object[]
	 */
	private function loadObjects(DriverInterface $driver, ScenarioInterface $scenario): array
	{
		$objectLoaders = $scenario->getObjectLoaders();

		if (0 >= count($objectLoaders)) {
			return [];
		}

		$this->logger->info('Loading objects from storage', [
			'entities' => array_values(array_unique(array_map(static function (ObjectLoaderInterface $objectLoader): string {
				return $objectLoader->getClassName();
			}, $objectLoaders))),
		]);

		$objects = [];

		foreach ($objectLoaders as $objectLoader) {
			$objects[] = $objectLoader->load($driver->getStorageDriver());
		}

		return array_merge([], ...$objects);
	}
}
