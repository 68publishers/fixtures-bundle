<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Instantiator\Chainable;

use LogicException;
use Nette\DI\Container;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;

final class ServiceFactoryInstantiator implements ChainableInstantiatorInterface
{
	use IsAServiceTrait;

	/** @var \Nette\DI\Container|NULL */
	private $container;

	/**
	 * @internal
	 *
	 * @param \Nette\DI\Container $container
	 *
	 * @return void
	 */
	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function canInstantiate(FixtureInterface $fixture): bool
	{
		$constructor = $fixture->getSpecs()->getConstructor();

		return NULL !== $constructor && $constructor instanceof MethodCallWithReference && $constructor->getCaller() instanceof InstantiatedReference;
	}

	/**
	 * {@inheritdoc}
	 */
	public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, GenerationContext $context): ResolvedFixtureSet
	{
		if (NULL === $this->container) {
			throw new LogicException(sprintf(
				'Missing a Container instance in an instantiator %s.',
				static::class
			));
		}

		/** @var \Nelmio\Alice\Definition\MethodCall\MethodCallWithReference $constructor */
		$constructor = $fixture->getSpecs()->getConstructor();
		/** @var \Nelmio\Alice\Definition\ServiceReference\InstantiatedReference $caller */
		$caller = $constructor->getCaller();

		$class = $fixture->getClassName();
		$method = $constructor->getMethod();
		$arguments = $constructor->getArguments() ?? [];
		$factory = $this->container->getService($caller->getId());

		$instance = $factory->$method(...$arguments);

		if (!$instance instanceof $class) {
			throw new InstantiationException(sprintf(
				'Instantiated fixture was expected to be an instance of "%s". Got "%s" instead.',
				$class,
				get_class($instance)
			));
		}

		$objects = $fixtureSet->getObjects()->with(
			new SimpleObject(
				$fixture->getId(),
				$instance
			)
		);

		return $fixtureSet->withObjects($objects);
	}
}
