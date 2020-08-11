<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface as FidryPersisterInterface;

interface PersisterInterface extends FidryPersisterInterface
{
	/**
	 * Returns storage driver/adapter e.g. EntityManager
	 *
	 * @return mixed
	 */
	public function getStorageDriver();
}
