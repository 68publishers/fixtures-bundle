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
use SixtyEightPublishers\FixturesBundle\Scenario\Scene\Scene;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface;
use SixtyEightPublishers\FixturesBundle\Scenario\Scene\SceneInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;
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
				return array_filter(array_map(static function (string $path) {
					return realpath($path);
				}, Helpers::expand($dirs, $this->getContainerBuilder()->parameters)));
			}),
			'scenarios' => Expect::arrayOf(Expect::structure([
				'purge_mode' => Expect::anyOf(...PurgeModeFactory::PURGE_MODES)->nullable(),

				# default scene:
				'fixtures' => Expect::listOf('string')->default([]),
				'object_loaders' => Expect::array()->before([$this, 'normalizeStatement']),

				# another scenes:
				'scenes' => Expect::arrayOf(Expect::structure([
					'decorator' => Expect::string()->nullable()->dynamic()->before([$this, 'normalizeStatement']),
					'fixtures' => Expect::listOf('string'),
					'object_loaders' => Expect::array()->before([$this, 'normalizeStatement']),
				])),
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

		$config['fixture_dirs'] = array_filter(array_map(static function (string $path) {
			return realpath($path);
		}, $config['fixture_dirs']));

		Validators::assertField($config, 'scenarios', 'array[]');

		foreach ($config['scenarios'] as $k => $scenarioConfig) {
			$scenarioConfig = $this->validateConfig([
				'purge_mode' => NULL,
				'fixtures' => [],
				'object_loaders' => [],
				'scenes' => [],
			], $scenarioConfig);

			Validators::assertField($scenarioConfig, 'purge_mode', 'string|null');
			Validators::assertField($scenarioConfig, 'fixtures', 'list');
			Validators::assertField($scenarioConfig, 'fixtures', 'string[]');
			Validators::assertField($scenarioConfig, 'object_loaders', 'list');
			Validators::assertField($scenarioConfig, 'scenes', 'array[]');

			if (NULL !== $scenarioConfig['purge_mode'] && !in_array($scenarioConfig['purge_mode'], PurgeModeFactory::PURGE_MODES, TRUE)) {
				throw new AssertionException(sprintf(
					'Invalid purge mode %s. Choose either "delete", "truncate" or "no_purge".',
					$scenarioConfig['purge_mode']
				));
			}

			$scenarioConfig['object_loaders'] = array_map([$this, 'normalizeStatement'], $scenarioConfig['object_loaders']);

			foreach ($scenarioConfig['scenes'] as $sceneKey => $sceneConfig) {
				$sceneConfig = $this->validateConfig([
					'decorator' => NULL,
					'fixtures' => [],
					'object_loaders' => [],
				], $sceneConfig);

				Validators::assertField($sceneConfig, 'decorator', 'null|string|' . Statement::class);
				Validators::assertField($sceneConfig, 'fixtures', 'list');
				Validators::assertField($sceneConfig, 'fixtures', 'string[]');
				Validators::assertField($sceneConfig, 'object_loaders', 'list');

				$sceneConfig['decorator'] = $this->normalizeStatement($sceneConfig['decorator']);
				$sceneConfig['object_loaders'] = array_map([$this, 'normalizeStatement'], $sceneConfig['object_loaders']);

				$scenarioConfig['scenes'][$sceneKey] = (object) $sceneConfig;
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

		/** @var \SixtyEightPublishers\FixturesBundle\DI\FixturesBundleContributorInterface $extension */
		foreach ($this->compiler->getExtensions(FixturesBundleContributorInterface::class) as $extension) {
			$configuration = new Configuration($this->getContainerBuilder(), $this->name);

			$extension->contributeToFixturesBundle($configuration);

			$name = $configuration->getBundleName();
			$dir = $configuration->getFixtureDir();

			if (NULL !== $name && NULL !== $dir) {
				$bundleFixtureDirs[$name] = $dir;
			}
		}

		$this->setServiceArgument($builder->getDefinition('fixtures_bundle.alice.bundle_map'), $bundleFixtureDirs, 0);

		$scenarios = [];

		foreach ($this->validConfig->scenarios as $name => $scenario) {
			$scenes = [];

			if (!empty($scenario->fixtures)) {
				$scenes[] = $builder->addDefinition($this->prefix('scenario.' . $name . '.scene._default'))
					->setType(SceneInterface::class)
					->setFactory(Scene::class, [
						$builder->getDefinition('68publishers_fixtures_bundle.file_resolver'),
						'default',
						$this->exportFixtureFiles($scenario->fixtures),
						$scenario->object_loaders,
					])
					->setAutowired(FALSE);
			}

			foreach ($scenario->scenes as $sceneName => $scene) {
				$sceneFactory = new Statement(Scene::class, [
					$builder->getDefinition('68publishers_fixtures_bundle.file_resolver'),
					$sceneName,
					$this->exportFixtureFiles($scene->fixtures),
					$scene->object_loaders,
				]);

				if (($decorator = $scene->decorator) instanceof Statement) {
					$decorator->arguments['scene'] = $sceneFactory;
					$sceneFactory = $decorator;
				}

				$scenes[] = $builder->addDefinition($this->prefix('scenario.' . $name . '.scene.' . $sceneName))
					->setType(SceneInterface::class)
					->setFactory($sceneFactory)
					->setAutowired(FALSE);
			}

			$scenarios[] = $builder->addDefinition($this->prefix('scenario.' . $name))
				->setType(ScenarioInterface::class)
				->setFactory(Scenario::class, [
					$name,
					$scenario->purge_mode,
					$scenes,
				])
				->setAutowired(FALSE);
		}

		$this->addServiceArguments($builder->getDefinition('68publishers_fixtures_bundle.scenario_provider.default'), NULL, $scenarios);
		$this->setServiceArgument($builder->getDefinition('68publishers_fixtures_bundle.file_resolver.default'), $this->validConfig->fixture_dirs, 2);
		$this->setServiceArgument($builder->getDefinition('68publishers_fixtures_bundle.file_resolver.relative'), $this->validConfig->fixture_dirs, 3);
	}

	/**
	 * @internal
	 *
	 * @param mixed $statement
	 *
	 * @return \Nette\DI\Definitions\Statement|null
	 */
	public function normalizeStatement($statement): ?Statement
	{
		if (NULL === $statement) {
			return NULL;
		}

		return $statement instanceof Statement ? $statement : new Statement($statement);
	}

	/**
	 * @param array $fixtures
	 *
	 * @return array
	 */
	private function exportFixtureFiles(array $fixtures): array
	{
		return array_map(static function (string $path) {
			$path = addslashes($path);

			return new PhpLiteral("'$path'");
		}, $fixtures);
	}
}
