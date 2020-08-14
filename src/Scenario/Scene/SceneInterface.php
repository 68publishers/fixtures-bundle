<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario\Scene;

use Psr\Log\LoggerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;

interface SceneInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return array
	 */
	public function getFixtures(): array;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 * @param \Psr\Log\LoggerInterface                                                             $logger
	 * @param \Fidry\AliceDataFixtures\Persistence\PurgeMode|null                                  $purgeMode
	 * @param array                                                                                $parameters
	 *
	 * @return void
	 */
	public function run(DriverInterface $driver, LoggerInterface $logger, ?PurgeMode $purgeMode = NULL, array $parameters = []): void;
}
