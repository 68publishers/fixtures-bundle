<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Flag\PreloadFlag;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Value\PreloadedUniqueValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;

final class PreloadedUniqueValueDenormalizer implements ValueDenormalizerInterface
{
	use IsAServiceTrait;

	/** @var \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface  */
	private $denormalizer;

	/**
	 * @param \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface $decoratedDenormalizer
	 */
	public function __construct(ValueDenormalizerInterface $decoratedDenormalizer)
	{
		$this->denormalizer = $decoratedDenormalizer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function denormalize(FixtureInterface $scope, FlagBag $flags = NULL, $value)
	{
		$value = $this->denormalizer->denormalize($scope, $flags, $value);
		$flag = $this->getPreloadFlag($flags);

		if (NULL === $flag) {
			return $value;
		}

		if ($value instanceof DynamicArrayValue) {
			return new DynamicArrayValue(
				$value->getQuantifier(),
				$this->resolvePreloadedUniqueValue($value->getValue(), $flag)
			);
		}

		if ($value instanceof ArrayValue) {
			$elements = $value->getValue();

			foreach ($elements as $key => $element) {
				$elements[$key] = $this->resolvePreloadedUniqueValue($element, $flag);
			}

			return new ArrayValue($elements);
		}

		return $this->resolvePreloadedUniqueValue($value, $flag);
	}

	/**
	 * @param \Nelmio\Alice\Definition\FlagBag|NULL $flags
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Flag\PreloadFlag|NULL
	 * @throws \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException
	 */
	private function getPreloadFlag(FlagBag $flags = NULL): ?PreloadFlag
	{
		if (NULL === $flags) {
			return NULL;
		}

		$preloadFlag = $uniqueFlag = NULL;

		foreach ($flags as $flag) {
			if ($flag instanceof UniqueFlag) {
				$uniqueFlag = $flag;

				continue;
			}

			if ($flag instanceof PreloadFlag) {
				$preloadFlag = $flag;
			}
		}

		if (NULL !== $preloadFlag && NULL === $uniqueFlag) {
			throw new InvalidScopeException('A flag "preload" can be used only with a flag "unique".');
		}

		return $preloadFlag;
	}

	/**
	 * @param mixed                                                                         $value
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Flag\PreloadFlag $flag
	 *
	 * @return mixed
	 */
	private function resolvePreloadedUniqueValue($value, PreloadFlag $flag)
	{
		if ($value instanceof UniqueValue) {
			return new PreloadedUniqueValue($value, $flag->getColumn());
		}

		return $value;
	}
}
