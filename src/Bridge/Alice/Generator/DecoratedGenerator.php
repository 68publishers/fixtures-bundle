<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator;

use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\GeneratorInterface;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Faker\Provider\ParametersProvider;

final class DecoratedGenerator implements GeneratorInterface
{
	use IsAServiceTrait;

	/** @var \Nelmio\Alice\GeneratorInterface  */
	private $generator;

	/** @var \Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface  */
	private $parameterBagResolver;

	/** @var \Faker\Generator  */
	private $fakerGenerator;

	/**
	 * @param \Nelmio\Alice\GeneratorInterface                               $generator
	 * @param \Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface $parameterBagResolver
	 * @param \Faker\Generator                                               $fakerGenerator
	 */
	public function __construct(GeneratorInterface $generator, ParameterBagResolverInterface $parameterBagResolver, FakerGenerator $fakerGenerator)
	{
		$this->generator = $generator;
		$this->parameterBagResolver = $parameterBagResolver;
		$this->fakerGenerator = $fakerGenerator;
	}

	/**
	 * @inheritdoc
	 */
	public function generate(FixtureSet $fixtureSet): ObjectSet
	{
		$provider = $this->getParametersProvider();

		if (NULL === $provider) {
			return $this->generator->generate($fixtureSet);
		}

		$provider->setParameterBag(
			$this->parameterBagResolver->resolve($fixtureSet->getLoadedParameters(), $fixtureSet->getInjectedParameters())
		);

		$fixtures = $this->generator->generate($fixtureSet);

		$provider->setParameterBag(new ParameterBag());

		return $fixtures;
	}

	/**
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Faker\Provider\ParametersProvider|NULL
	 */
	private function getParametersProvider(): ?ParametersProvider
	{
		$providers = array_filter($this->fakerGenerator->getProviders(), static function ($provider) {
			return $provider instanceof ParametersProvider;
		});

		return 0 < count($providers) ? array_shift($providers) : NULL;
	}
}
