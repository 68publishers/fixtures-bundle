<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

interface ScenarioProviderInterface extends \IteratorAggregate
{
	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface
	 */
	public function getScenario(string $name): ScenarioInterface;
}
