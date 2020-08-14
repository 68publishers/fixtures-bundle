<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PersisterInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\ContextInterface;

final class ComposedDriver implements DriverInterface
{
	/** @var string  */
	private $name;

	/** @var \Fidry\AliceDataFixtures\LoaderInterface  */
	private $loader;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PersisterInterface  */
	private $persister;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\ContextInterface */
	private $context;

	/**
	 * @param string                                                                                        $name
	 * @param \Fidry\AliceDataFixtures\LoaderInterface                                                      $loader
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PersisterInterface  $persister
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\ContextInterface $context
	 */
	public function __construct(string $name, LoaderInterface $loader, PersisterInterface $persister, ContextInterface $context)
	{
		$this->name = $name;
		$this->loader = $loader;
		$this->persister = $persister;
		$this->context = $context;
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
		$this->context->bindContext(LoadContext::class, $this->getName());

		$fixtures = $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);

		$this->context->unbindContext(LoadContext::class);

		return $fixtures;
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
	public function getStorageDriver()
	{
		return $this->persister->getStorageDriver();
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): void
	{
		$this->persister->clear();
	}
}
