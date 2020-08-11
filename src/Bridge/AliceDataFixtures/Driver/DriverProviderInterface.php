<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

interface DriverProviderInterface
{
	/**
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface
	 */
	public function getDriver(?string $name = NULL): DriverInterface;
}
