<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Fidry\AliceDataFixtures\FileResolverInterface;
use SixtyEightPublishers\FixturesBundle\Scenario\IScenario;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver;

final class DefaultLoader implements ILoader
{
	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/** @var \Psr\Log\LoggerInterface  */
	private $logger;

	/** @var array  */
	private $parameters = [];

	/**
	 * DefaultLoader constructor.
	 *
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
	public function load(IDriver $driver, IScenario $scenario): array
	{
		$this->logger->info(sprintf(
			'Loading fixtures for scenario "%s"',
			$scenario->getName()
		));

		$fixtures = $driver->load(
			$this->fileResolver->resolve($scenario->getFixtures()),
			$this->parameters,
			[],
			$scenario->getPurgeMode() ? $scenario->getPurgeMode()->getMode() : NULL
		);

		$this->logger->info('fixtures loaded');

		return $fixtures;
	}
}
