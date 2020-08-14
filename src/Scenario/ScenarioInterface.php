<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use IteratorAggregate;
use Psr\Log\LoggerInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;

interface ScenarioInterface extends IteratorAggregate
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|NULL
	 */
	public function getPurgeMode(): ?NamedPurgeMode;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|NULL $purgeMode
	 *
	 * @return void
	 */
	public function setPurgeMode(?NamedPurgeMode $purgeMode): void;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 * @param \Psr\Log\LoggerInterface                                                             $logger
	 * @param array                                                                                $parameters
	 *
	 * @return void
	 */
	public function run(DriverInterface $driver, LoggerInterface $logger, array $parameters = []): void;
}
