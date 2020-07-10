<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use InvalidArgumentException;

final class ScenarioProvider implements IScenarioProvider
{
	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\IScenario[]  */
	private $scenarios = [];

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenario[] $scenarios
	 */
	public function __construct(array $scenarios)
	{
		foreach ($scenarios as $scenario) {
			$this->addScenario($scenario);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getScenario(string $name): IScenario
	{
		if (!isset($this->scenarios[$name])) {
			throw new InvalidArgumentException(sprintf(
				'Missing scenario with name "%s". %s',
				$name,
				0 >= count($this->scenarios) ? 'No scenarios found.' : "Available scenarios are: \n- " . implode("\n- ", array_keys($this->scenarios))
			));
		}

		return $this->scenarios[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->scenarios);
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenario $scenario
	 *
	 * @return void
	 */
	private function addScenario(IScenario $scenario): void
	{
		$this->scenarios[$scenario->getName()] = $scenario;
	}
}
