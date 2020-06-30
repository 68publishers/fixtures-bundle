<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;

interface IDriver extends LoaderInterface, PurgerFactoryInterface, PersisterInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;
}
