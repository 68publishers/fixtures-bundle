<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader;

use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;

interface LoaderInterface
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface                      $scenario
	 *
	 * @return array
	 */
	public function load(DriverInterface $driver, ScenarioInterface $scenario): array;
}
