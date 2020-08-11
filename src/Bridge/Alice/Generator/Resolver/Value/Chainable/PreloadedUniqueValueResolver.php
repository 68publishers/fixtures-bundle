<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Value\PreloadedUniqueValue;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\UniqueValuePreloaderInterface;

final class PreloadedUniqueValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
	use IsAServiceTrait;

	/** @var \Nelmio\Alice\Generator\Resolver\UniqueValuesPool  */
	private $pool;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\UniqueValuePreloaderInterface  */
	private $uniqueValuePreloader;

	/** @var \Nelmio\Alice\Generator\ValueResolverInterface|NULL  */
	private $resolver;

	/** @var bool[]  */
	private $preloadedIdentifiers = [];

	/**
	 * @param \Nelmio\Alice\Generator\Resolver\UniqueValuesPool                                                            $pool
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\UniqueValuePreloaderInterface $uniqueValuePreloader
	 * @param \Nelmio\Alice\Generator\ValueResolverInterface|NULL                                                          $resolver
	 */
	public function __construct(UniqueValuesPool $pool, UniqueValuePreloaderInterface $uniqueValuePreloader, ValueResolverInterface $resolver = NULL)
	{
		$this->pool = $pool;
		$this->uniqueValuePreloader = $uniqueValuePreloader;
		$this->resolver = $resolver;
	}

	/**
	 * {@inheritDoc}
	 */
	public function canResolve(ValueInterface $value): bool
	{
		return $value instanceof PreloadedUniqueValue;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withValueResolver(ValueResolverInterface $resolver)
	{
		return new self($this->pool, $this->uniqueValuePreloader, $resolver);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Value\PreloadedUniqueValue $value
	 */
	public function resolve(ValueInterface $value, FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, array $scope, GenerationContext $context): ResolvedValueWithFixtureSet
	{
		$this->checkResolver(__METHOD__);

		/** @var \Nelmio\Alice\Definition\Value\UniqueValue $uniqueValue */
		$uniqueValue = $value->getValue();
		$uniqueValueId = $uniqueValue->getId();

		if (!isset($this->preloadedIdentifiers[$uniqueValueId])) {
			$this->preloadUniqueValues($value, $fixture->getClassName());

			$this->preloadedIdentifiers[$uniqueValueId] = TRUE;
		}

		return $this->resolver->resolve($uniqueValue, $fixture, $fixtureSet, $scope, $context);
	}

	/**
	 * @param string $checkedMethod
	 *
	 * @return void
	 */
	private function checkResolver(string $checkedMethod): void
	{
		if (NULL === $this->resolver) {
			throw ResolverNotFoundExceptionFactory::createUnexpectedCall($checkedMethod);
		}
	}

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Value\PreloadedUniqueValue $value
	 * @param string                                                                                  $className
	 *
	 * @return void
	 */
	private function preloadUniqueValues(PreloadedUniqueValue $value, string $className): void
	{
		$uniqueValue = $value->getValue();

		foreach ($this->uniqueValuePreloader->preload($className, $value->getColumn()) as $preloadedValue) {
			$this->pool->add($uniqueValue->withValue($preloadedValue));
		}
	}
}
