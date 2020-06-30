<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

interface IDriverProvider
{
	/**
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver
	 */
	public function getDriver(?string $name = NULL): IDriver;
}
