<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Validators;
use Nette\DI\Definitions\Statement;
use Nette\Utils\AssertionException;
use Nette\DI\Definitions\ServiceDefinition;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension;

final class NelmioAliceExtension extends CompilerExtension
{
	public const    TAG_NELMIO_ALICE_FAKER_PROVIDER = 'nelmio_alice.faker.provider',
					TAG_NELMIO_ALICE_FILE_PARSER = 'nelmio_alice.file_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_EXPRESSION_LANGUAGE_CHAINABLE_TOKEN_PARSER = 'nelmio_alice.fixture_builder.expression_language.chainable_token_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FLAG_PARSER = 'nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FIXTUE_DENORMALIZER = 'nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_METHOD_FLAG_HANDLER = 'nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler',
					TAG_NELMIO_ALICE_GENERATOR_CALLLER_CHAINABLE_CALL_PROCESSOR = 'nelmio_alice.generator.caller.chainable_call_processor',
					TAG_NELMIO_ALICE_GENERATOR_INSTANTIATOR_CHAINABLE_INSTANTIATOR = 'nelmio_alice.generator.instantiator.chainable_instantiator',
					TAG_NELMIO_ALICE_GENERATOR_RESOLVER_PARAMETER_CHAINABLE_RESOLVER = 'nelmio_alice.generator.resolver.parameter.chainable_resolver',
					TAG_NELMIO_ALICE_GENERATOR_RESOLVER_VALUE_CHAINABLE_RESOLVER= 'nelmio_alice.generator.resolver.value.chainable_resolver';

	/**
	 * @return \Nette\Schema\Schema
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'locale' => Expect::string('en_US'),
			'seed' => Expect::int(1)->min(1.0)->nullable(),
			'loading_limit' => Expect::int(5),
			'functions_blacklist' => Expect::array(['current']),
			'max_unique_values_retry' => Expect::int(150),
		]);
	}

	/**
	 * {@inheritDoc}
	 * @throws \Nette\Utils\AssertionException
	 */
	protected function getNette24Config(): object
	{
		$config = $this->validateConfig([
			'locale' => 'en_US',
			'seed' => 1,
			'loading_limit' => 5,
			'functions_blacklist' => ['current'],
			'max_unique_values_retry' => 150,
		]);

		Validators::assertField($config, 'locale', 'string');
		Validators::assertField($config, 'seed', 'int|null');

		if (is_int($config['seed']) && 1 > $config['seed']) {
			throw new AssertionException('The value of an option "seed" must be a null or an integer higher than 0.');
		}

		Validators::assertField($config, 'functions_blacklist', 'string[]');
		Validators::assertField($config, 'loading_limit', 'int');
		Validators::assertField($config, 'max_unique_values_retry', 'int');

		if (1 > $config['loading_limit']) {
			throw new AssertionException('The value of an option "loading_limit" must be an integer higher than 0.');
		}

		if (1 > $config['max_unique_values_retry']) {
			throw new AssertionException('The value of an option "max_unique_values_retry" must be an integer higher than 0.');
		}

		return (object) $config;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doLoadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		if (!$builder->hasDefinition('property_accessor')) {
			$builder->addDefinition($this->prefix('default_property_accessor'))
				->setType(PropertyAccessorInterface::class)
				->setFactory(new Statement([new Statement([new Statement([PropertyAccess::class, 'createPropertyAccessorBuilder']), 'enableMagicCall']), 'getPropertyAccessor']));

			$builder->addAlias('property_accessor', $this->prefix('default_property_accessor'));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var ServiceDefinition $faker */
		$faker = $builder->getDefinition('nelmio_alice.faker.generator');

		$faker->setArguments([$this->validConfig->locale])
			->addSetup('seed', [$this->validConfig->seed]);

		foreach ($this->findServicesByTag(self::TAG_NELMIO_ALICE_FAKER_PROVIDER) as $service) {
			$faker->addSetup('addProvider', [$service]);
		}

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.file_parser.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FILE_PARSER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FIXTURE_BUILDER_EXPRESSION_LANGUAGE_CHAINABLE_TOKEN_PARSER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.fixture_builder.denormalizer.flag_parser.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FLAG_PARSER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FIXTUE_DENORMALIZER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_METHOD_FLAG_HANDLER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.generator.caller.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_GENERATOR_CALLLER_CHAINABLE_CALL_PROCESSOR)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.generator.instantiator.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_GENERATOR_INSTANTIATOR_CHAINABLE_INSTANTIATOR)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.generator.resolver.parameter.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_GENERATOR_RESOLVER_PARAMETER_CHAINABLE_RESOLVER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.generator.resolver.value.registry'),
			NULL,
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_GENERATOR_RESOLVER_VALUE_CHAINABLE_RESOLVER)
		);

		$this->setServiceArgument(
			$builder->getDefinition('nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver'),
			$this->validConfig->loading_limit,
			1
		);

		$this->setServiceArgument(
			$builder->getDefinition('nelmio_alice.generator.resolver.value.chainable.php_value_resolver'),
			$this->validConfig->functions_blacklist,
			0
		);

		$this->setServiceArgument(
			$builder->getDefinition('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver'),
			$this->validConfig->max_unique_values_retry,
			2
		);
	}
}
