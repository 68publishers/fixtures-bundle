<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface as FidryPersisterInterface;

interface PersisterInterface extends FidryPersisterInterface
{
	/**
	 * Returns a storage driver/adapter e.g. EntityManager
	 *
	 * @return mixed
	 */
	public function getStorageDriver();

	/**
	 * Clear the driver's/adapter's state - remove already processed references to avoid a memory leaks
	 */
	public function clear(): void;
}
