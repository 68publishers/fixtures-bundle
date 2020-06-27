<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI;

use Nette\DI\Definitions\Statement;
use Nette\DI\Definitions\ServiceDefinition;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AbstractBridgeExtension;

abstract class AbstractNelmioAliceExtension extends AbstractBridgeExtension
{
	public const    TAG_NELMIO_ALICE_FAKER_PROVIDER = 'nelmio_alice.faker.provider',
					TAG_NELMIO_ALICE_FILE_PARSER = 'nelmio_alice.file_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_EXPRESSION_LANGUAGE_CHAINABLE_TOKEN_PARSER = 'nelmio_alice.fixture_builder.expression_language.chainable_token_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FLAG_PARSER = 'nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FIXTUE_NEMORMALIZER = 'nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer',
					TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_METHOD_FLAG_HANDLER = 'nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler',
					TAG_NELMIO_ALICE_GENERATOR_CALLLER_CHAINABLE_CALL_PROCESSOR = 'nelmio_alice.generator.caller.chainable_call_processor',
					TAG_NELMIO_ALICE_GENERATOR_INSTANTIATOR_CHAINABLE_INSTANTIATOR = 'nelmio_alice.generator.instantiator.chainable_instantiator',
					TAG_NELMIO_ALICE_GENERATOR_RESOLVER_PARAMETER_CHAINABLE_RESOLVER = 'nelmio_alice.generator.resolver.parameter.chainable_resolver',
					TAG_NELMIO_ALICE_GENERATOR_RESOLVER_VALUE_CHAINABLE_RESOLVER= 'nelmio_alice.generator.resolver.value.chainable_resolver';

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
			$builder->getDefinition('nelmio_alice.fixture_builder.expression_language.parser.token_parser'),
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
			$this->findServicesByTag(self::TAG_NELMIO_ALICE_FIXTURE_BUILDER_DENORMALIZER_CHAINABLE_FIXTUE_NEMORMALIZER)
		);

		$this->addServiceArguments(
			$builder->getDefinition('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
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
			$builder->getDefinition('nelmio_alice.generator.resolver.value'),
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
