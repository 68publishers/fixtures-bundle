<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader;

use SixtyEightPublishers\FixturesBundle\Scenario\IScenario;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver;

interface ILoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver $driver
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenario                      $scenario
	 *
	 * @return array
	 */
	public function load(IDriver $driver, IScenario $scenario): array;
}
