<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI;

use Nette\DI\Statement;
use Nette\Utils\Finder;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class NelmioAlice24Extension extends CompilerExtension
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

	/** @var array  */
	private $defaults = [
		'locale' => 'en_US',
		'seed' => 1,
		'loading_limit' => 5,
		'functions_blacklist' => ['current'],
		'max_unique_values_retry' => 150,
	];

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		if (!$builder->hasDefinition('property_accessor')) {
			$builder->addDefinition($this->prefix('default_property_accessor'))
				->setType(PropertyAccessorInterface::class)
				->setFactory(new Statement([new Statement([new Statement([PropertyAccess::class, 'createPropertyAccessorBuilder']), 'enableMagicCall']), 'getPropertyAccessor']));

			$builder->addAlias('property_accessor', $this->prefix('default_property_accessor'));
		}

		foreach (Finder::findFiles('*.neon')->from(__DIR__ . '/../config') as $filename => $_) {
			$this->compiler::loadDefinitions($builder, $this->loadFromFile($filename)['services']);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$faker = $builder->getDefinition('nelmio_alice.faker.generator');

		$faker->setArguments([$config['locale']])
			->addSetup('seed', [$config['seed']]);

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
			$config['loading_limit'],
			1
		);

		$this->setServiceArgument(
			$builder->getDefinition('nelmio_alice.generator.resolver.value.chainable.php_value_resolver'),
			$config['functions_blacklist'],
			0
		);

		$this->setServiceArgument(
			$builder->getDefinition('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver'),
			$config['max_unique_values_retry'],
			2
		);
	}

	/**
	 * @param string $tag
	 *
	 * @return \Nette\DI\ServiceDefinition[]
	 */
	private function findServicesByTag(string $tag): array
	{
		$builder = $this->getContainerBuilder();

		return array_map(static function ($name) use ($builder) {
			return $builder->getDefinition($name);
		}, array_keys($builder->findByTag($tag)));
	}

	/**
	 * @param \Nette\DI\ServiceDefinition $definition
	 * @param int|null                    $index
	 * @param mixed                       ...$args
	 *
	 * @return void
	 */
	private function addServiceArguments(ServiceDefinition $definition, ?int $index = NULL, ...$args): void
	{
		if (NULL !== $definition->getFactory()) {
			$currentArguments = $definition->getFactory()->arguments;

			if (NULL === $index) {
				$args = array_merge($currentArguments, $args);
			} else {
				$pre = array_slice($currentArguments, 0, $index);
				$post = array_slice($currentArguments, $index);

				$args = array_merge($pre, $args, $post);
			}
		}

		$definition->setArguments($args);
	}

	/**
	 * @param \Nette\DI\ServiceDefinition $definition
	 * @param $arg
	 * @param int $index
	 *
	 * @return void
	 */
	private function setServiceArgument(ServiceDefinition $definition, $arg, int $index): void
	{
		$args = NULL !== $definition->getFactory() ? $definition->getFactory()->arguments : [];
		$keys = array_keys($args);

		$args[$keys[$index] ?? $index] = $arg;

		$definition->setArguments($args);
	}
}
