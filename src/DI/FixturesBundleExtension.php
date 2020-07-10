<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\DI;

use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Validators;
use Nette\PhpGenerator\PhpLiteral;
use Nette\DI\Definitions\Statement;
use Nette\Utils\AssertionException;
use SixtyEightPublishers\FixturesBundle\Scenario\Scenario;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\Configuration;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\IFixturesBundleContributor;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI\FidryAliceDataFixturesExtension;

final class FixturesBundleExtension extends CompilerExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'fixture_dirs' => Expect::arrayOf('string')->default(['%appDir%/fixtures', '%appDir%/../fixtures'])->before(function (array $dirs) {
				return array_map(static function (string $path) {
					return realpath($path);
				}, Helpers::expand($dirs, $this->getContainerBuilder()->parameters));
			}),
			'scenarios' => Expect::arrayOf(Expect::structure([
				'purge_mode' => Expect::anyOf(...PurgeModeFactory::PURGE_MODES)->nullable(),
				'fixtures' => Expect::listOf('string'),
			])),
		]);
	}

	/**
	 * {@inheritDoc}
	 * @throws \Nette\Utils\AssertionException
	 */
	protected function getNette24Config(): object
	{
		$config = Helpers::expand($this->validateConfig([
			'fixture_dirs' => ['%appDir%/fixtures', '%appDir%/../fixtures'],
			'scenarios' => [],
		]), $this->getContainerBuilder()->parameters);

		Validators::assertField($config, 'fixture_dirs', 'string[]');

		$config['fixture_dirs'] = array_map(static function (string $path) {
			return realpath($path);
		}, $config['fixture_dirs']);

		Validators::assertField($config, 'scenarios', 'array[]');

		foreach ($config['scenarios'] as $k => $scenarioConfig) {
			$scenarioConfig = $this->validateConfig([
				'purge_mode' => NULL,
				'fixtures' => [],
			], $scenarioConfig);

			Validators::assertField($scenarioConfig, 'purge_mode', 'string|null');
			Validators::assertField($scenarioConfig, 'fixtures', 'list');
			Validators::assertField($scenarioConfig, 'fixtures', 'string[]');

			if (NULL !== $scenarioConfig['purge_mode'] && !in_array($scenarioConfig['purge_mode'], PurgeModeFactory::PURGE_MODES, TRUE)) {
				throw new AssertionException(sprintf(
					'Invalid purge mode %s. Choose either "delete", "truncate" or "no_purge".',
					$scenarioConfig['purge_mode']
				));
			}

			$config['scenarios'][$k] = (object) $scenarioConfig;
		}

		return (object) $config;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doLoadConfiguration(): void
	{
		$this->checkExtension(NelmioAliceExtension::class);
		$this->checkExtension(FidryAliceDataFixturesExtension::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$bundleFixtureDirs = [];
		$builder = $this->getContainerBuilder();

		/** @var \SixtyEightPublishers\FixturesBundle\Bridge\Nette\IFixturesBundleContributor $extension */
		foreach ($this->compiler->getExtensions(IFixturesBundleContributor::class) as $extension) {
			$configuration = new Configuration($this->getContainerBuilder(), $this->name);

			$extension->contributeToFixturesBundle($configuration);

			$name = $configuration->getBundleName();
			$dir = $configuration->getFixtureDir();

			if (NULL !== $name && NULL !== $dir) {
				$bundleFixtureDirs[$name] = $dir;
			}
		}

		$this->setServiceArgument($builder->getDefinition('fixtures_bundle.alice.bundle_map'), $bundleFixtureDirs, 0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$scenarios = [];

		foreach ($this->validConfig->scenarios as $name => $scenario) {
			$scenarios[] = new Statement(Scenario::class, [
				$name,
				array_map(static function (string $path) {
					$path = addslashes($path);

					return new PhpLiteral("'$path'");
				}, $scenario->fixtures),
				$scenario->purge_mode,
			]);
		}

		$this->addServiceArguments($builder->getDefinition('68publishers_fixtures_bundle.scenario_provider.default'), NULL, $scenarios);
		$this->setServiceArgument($builder->getDefinition('68publishers_fixtures_bundle.file_resolver.default'), $this->validConfig->fixture_dirs, 2);
		$this->setServiceArgument($builder->getDefinition('68publishers_fixtures_bundle.file_resolver.relative'), $this->validConfig->fixture_dirs, 3);
	}
}
