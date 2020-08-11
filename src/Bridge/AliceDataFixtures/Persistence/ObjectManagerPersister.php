<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use Nelmio\Alice\IsAServiceTrait;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\Persister\ObjectManagerPersister as FidryObjectManagerPersister;

final class ObjectManagerPersister extends FidryObjectManagerPersister implements PersisterInterface
{
	use IsAServiceTrait;

	/** @var \Doctrine\Common\Persistence\ObjectManager  */
	private $manager;

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 */
	public function __construct(ObjectManager $manager)
	{
		parent::__construct($manager);

		$this->manager = $manager;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Doctrine\Common\Persistence\ObjectManager
	 */
	public function getStorageDriver(): ObjectManager
	{
		return $this->manager;
	}
}
