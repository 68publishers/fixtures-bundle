<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;

final class ComposedDriver implements IDriver
{
	/** @var string  */
	private $name;

	/** @var \Fidry\AliceDataFixtures\LoaderInterface  */
	private $loader;

	/** @var \Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface  */
	private $purgerFactory;

	/** @var \Fidry\AliceDataFixtures\Persistence\PersisterInterface  */
	private $persister;

	/**
	 * @param string                                                      $name
	 * @param \Fidry\AliceDataFixtures\LoaderInterface                    $loader
	 * @param \Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface $purgerFactory
	 * @param \Fidry\AliceDataFixtures\Persistence\PersisterInterface     $persister
	 */
	public function __construct(string $name, LoaderInterface $loader, PurgerFactoryInterface $purgerFactory, PersisterInterface $persister)
	{
		$this->name = $name;
		$this->loader = $loader;
		$this->purgerFactory = $purgerFactory;
		$this->persister = $persister;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = NULL): array
	{
		return $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);
	}

	/**
	 * {@inheritDoc}
	 */
	public function persist($object)
	{
		return $this->persister->persist($object);
	}

	/**
	 * {@inheritDoc}
	 */
	public function flush()
	{
		return $this->persister->flush();
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(PurgeMode $mode, PurgerInterface $purger = NULL): PurgerInterface
	{
		return $this->purgerFactory->create($mode, $purger);
	}
}
