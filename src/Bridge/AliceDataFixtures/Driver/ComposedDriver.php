<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext;

final class ComposedDriver implements IDriver
{
	/** @var string  */
	private $name;

	/** @var \Fidry\AliceDataFixtures\LoaderInterface  */
	private $loader;

	/** @var \Fidry\AliceDataFixtures\Persistence\PersisterInterface  */
	private $persister;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext */
	private $context;

	/**
	 * @param string                                                                                $name
	 * @param \Fidry\AliceDataFixtures\LoaderInterface                                              $loader
	 * @param \Fidry\AliceDataFixtures\Persistence\PersisterInterface                               $persister
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext $context
	 */
	public function __construct(string $name, LoaderInterface $loader, PersisterInterface $persister, IContext $context)
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
}
