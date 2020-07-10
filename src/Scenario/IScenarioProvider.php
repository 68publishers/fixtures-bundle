<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

interface IScenarioProvider extends \IteratorAggregate
{
	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Scenario\IScenario
	 */
	public function getScenario(string $name): IScenario;
}
