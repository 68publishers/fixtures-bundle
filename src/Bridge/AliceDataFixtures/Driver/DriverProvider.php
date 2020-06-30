<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use InvalidArgumentException;

final class DriverProvider implements IDriverProvider
{
	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver[] */
	private $drivers = [];

	/** @var string  */
	private $defaultName;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver[] $drivers
	 * @param string                                                                         $defaultName
	 */
	public function __construct(array $drivers, string $defaultName)
	{
		foreach ($drivers as $driver) {
			$this->addDriver($driver);
		}

		$this->defaultName = $defaultName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDriver(?string $name = NULL): IDriver
	{
		$driver = $this->drivers[$name ?? $this->defaultName] ?? NULL;

		if (NULL === $driver) {
			throw new InvalidArgumentException(NULL === $name ? 'Missing default driver ' . $this->defaultName : 'Missing driver with name ' . $name);
		}

		return $driver;
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver $driver
	 *
	 * @return void
	 */
	private function addDriver(IDriver $driver): void
	{
		$this->drivers[$driver->getName()] = $driver;
	}
}
