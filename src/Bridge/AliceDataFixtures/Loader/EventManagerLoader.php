<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Loader;

use Doctrine\Common\EventManager;
use Nelmio\Alice\IsAServiceTrait;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager\IEventManagerRestrictorFactory;

final class EventManagerLoader implements LoaderInterface, PersisterAwareInterface
{
	use IsAServiceTrait;

	/** @var \Fidry\AliceDataFixtures\LoaderInterface  */
	private $loader;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager\IEventManagerRestrictorFactory  */
	private $eventManagerRestrictorFactory;

	/** @var \Doctrine\Common\EventManager  */
	private $eventManager;

	/**
	 * @param \Fidry\AliceDataFixtures\LoaderInterface                                                                  $decoratedLoader
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager\IEventManagerRestrictorFactory $eventManagerRestrictorFactory
	 * @param \Doctrine\Common\EventManager                                                                             $eventManager
	 */
	public function __construct(LoaderInterface $decoratedLoader, IEventManagerRestrictorFactory $eventManagerRestrictorFactory, EventManager $eventManager)
	{
		$this->loader = $decoratedLoader;
		$this->eventManagerRestrictorFactory = $eventManagerRestrictorFactory;
		$this->eventManager = $eventManager;
	}

	/**
	 * @inheritdoc
	 */
	public function withPersister(PersisterInterface $persister): self
	{
		$loader = $this->loader;

		if ($loader instanceof PersisterAwareInterface) {
			$loader = $loader->withPersister($persister);
		}

		return new self($loader, $this->eventManagerRestrictorFactory, $this->eventManager);
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = NULL): array
	{
		$restrictor = $this->eventManagerRestrictorFactory->create($this->eventManager);

		$restrictor->restrict();

		return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
	}
}
