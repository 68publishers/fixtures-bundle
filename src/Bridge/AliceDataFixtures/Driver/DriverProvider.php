<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use InvalidArgumentException;

final class DriverProvider implements DriverProviderInterface
{
	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface[] */
	private $drivers = [];

	/** @var string  */
	private $defaultName;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface[] $drivers
	 * @param string                                                                                 $defaultName
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
	public function getDriver(?string $name = NULL): DriverInterface
	{
		$driver = $this->drivers[$name ?? $this->defaultName] ?? NULL;

		if (NULL === $driver) {
			throw new InvalidArgumentException(NULL === $name ? 'Missing default driver ' . $this->defaultName : 'Missing driver with name ' . $name);
		}

		return $driver;
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface $driver
	 *
	 * @return void
	 */
	private function addDriver(DriverInterface $driver): void
	{
		$this->drivers[$driver->getName()] = $driver;
	}
}
