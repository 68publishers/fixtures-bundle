<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\Bridge\Alice\DI;

use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use SixtyEightPublishers\FixturesBundle\Tests\Helper\ContainerFactory;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;

require __DIR__ . '/../../../../bootstrap.php';

final class NelmioAliceExtensionIntegrationTest extends TestCase
{
	/** @var \Nette\DI\Container|NULL */
	private $container;

	protected function setUp(): void
	{
		$this->container = ContainerFactory::createContainer(__METHOD__, __DIR__ . '/../../../../resources/config/nelmio_alice_integration_test.neon');
	}

	public function testLoadersAreDefinedInTheConfiguration(): void
	{
		Assert::type(DataLoaderInterface::class, $this->container->getByType(DataLoaderInterface::class, FALSE));
		Assert::type(FileLoaderInterface::class, $this->container->getByType(FileLoaderInterface::class, FALSE));
		Assert::type(FilesLoaderInterface::class, $this->container->getByType(FilesLoaderInterface::class, FALSE));
	}

	public function testResolverUsesTheLimitIsDefinedInTheConfiguration(): void
	{
		/** @var \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver $resolver */
		$resolver = $this->container->getService('nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver');

		Assert::type(RecursiveParameterResolver::class, $resolver);

		$limitReflection = (new \ReflectionClass(RecursiveParameterResolver::class))->getProperty('limit');
		$limitReflection->setAccessible(TRUE);

		Assert::equal(50, $limitReflection->getValue($resolver));
	}

	public function testUniqueValueResolverUsesTheLimitIsDefinedInTheConfiguration(): void
	{
		/** @var \Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver $resolver */
		$resolver = $this->container->getService('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver');

		Assert::type(UniqueValueResolver::class, $resolver);

		$limitReflection = (new \ReflectionClass(UniqueValueResolver::class))->getProperty('limit');
		$limitReflection->setAccessible(TRUE);

		Assert::equal(15, $limitReflection->getValue($resolver));
	}

	public function testUniqueValueResolverUsesTheSeedAndLocaleIsDefinedInTheConfiguration(): void
	{
		/** @var \Faker\Generator $generator */
		$generator = $this->container->getService('nelmio_alice.faker.generator');

		Assert::type(FakerGenerator::class, $generator);

		$this->assertGeneratorLocaleIs('fr_FR', $generator);
		$this->assertHasAliceProvider($generator);
	}

	private function assertGeneratorLocaleIs(string $locale, FakerGenerator $generator): void
	{
		$providers = $generator->getProviders();
		$regex = sprintf('/^Faker\\\Provider\\\%s\\\.*/', $locale);

		foreach ($providers as $provider) {
			if (preg_match($regex, get_class($provider))) {
				return;
			}
		}

		throw new RuntimeException(sprintf('Generator has not been initialised with the locale "%s".', $locale));
	}

	private function assertHasAliceProvider(FakerGenerator $generator): void
	{
		$providers = $generator->getProviders();

		foreach ($providers as $provider) {
			if ($provider instanceof AliceProvider) {
				return;
			}
		}

		throw new RuntimeException(sprintf('Generator does not have the provider "%s".', AliceProvider::class));
	}
}

(new NelmioAliceExtensionIntegrationTest())->run();
