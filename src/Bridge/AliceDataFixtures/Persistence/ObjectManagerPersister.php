<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use Nelmio\Alice\IsAServiceTrait;
use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister as FidryObjectManagerPersister;

final class ObjectManagerPersister extends FidryObjectManagerPersister implements PersisterInterface
{
	use IsAServiceTrait;

	/** @var \Doctrine\Persistence\ObjectManager  */
	private $manager;

	/**
	 * @param \Doctrine\Persistence\ObjectManager $manager
	 */
	public function __construct(ObjectManager $manager)
	{
		parent::__construct($manager);

		$this->manager = $manager;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Doctrine\Persistence\ObjectManager
	 */
	public function getStorageDriver(): ObjectManager
	{
		return $this->manager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): void
	{
		$this->manager->clear();
	}
}
