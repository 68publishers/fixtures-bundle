<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI;

use Generator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Validators;
use Psr\Log\LoggerInterface;
use Nette\Utils\AssertionException;
use Nette\DI\MissingServiceException;
use Fidry\AliceDataFixtures\ProcessorInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriver;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverProvider;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriverProvider;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Logger\LoggerDecorator;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;

final class FidryAliceDataFixturesExtension extends CompilerExtension
{
	public const DOCTRINE_ORM_DRIVER = 'doctrine_orm';
	public const DOCTRINE_MONGODB_ODM_DRIVER = 'doctrine_mongodb_odm';
	public const DOCTRINE_PHPCR_ODM_DRIVER = 'doctrine_phpcr_odm';

	public const TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR = 'fidry_alice_data_fixtures.processor';

	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'default_purge_mode' => Expect::anyOf(...PurgeModeFactory::PURGE_MODES)->default(PurgeModeFactory::PURGE_MODE_DELETE)->dynamic(),
			'default_driver' => Expect::string(self::DOCTRINE_ORM_DRIVER),
			'db_drivers' => Expect::structure([
				self::DOCTRINE_ORM_DRIVER => Expect::bool(FALSE),
				self::DOCTRINE_MONGODB_ODM_DRIVER => Expect::bool(FALSE),
				self::DOCTRINE_PHPCR_ODM_DRIVER => Expect::bool(FALSE),
			]),
			'event_listeners' => Expect::structure([
				'allow_all' => Expect::bool(TRUE),
				'excluded' => Expect::arrayOf('string')->default([]),
			]),
		]);
	}

	/**
	 * {@inheritDoc}
	 * @throws \Nette\Utils\AssertionException
	 */
	protected function getNette24Config(): object
	{
		$config = $this->validateConfig([
			'default_purge_mode' => PurgeModeFactory::PURGE_MODE_DELETE, # 'delete', 'truncate', 'no_purge'
			'default_driver' => self::DOCTRINE_ORM_DRIVER,
			'db_drivers' => [
				self::DOCTRINE_ORM_DRIVER => FALSE,
				self::DOCTRINE_MONGODB_ODM_DRIVER => FALSE,
				self::DOCTRINE_PHPCR_ODM_DRIVER => FALSE,
			],
			'event_listeners' => [
				'allow_all' => TRUE,
				'excluded' => [],
			],
		]);

		Validators::assertField($config, 'default_purge_mode', 'string');

		if (!in_array($config['default_purge_mode'], PurgeModeFactory::PURGE_MODES, TRUE)) {
			throw new AssertionException(sprintf(
				'Invalid purge mode %s. Choose either "delete", "truncate" or "no_purge".',
				$config['default_purge_mode']
			));
		}

		Validators::assertField($config, 'default_driver', 'string');
		Validators::assertField($config, 'db_drivers', 'array');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_ORM_DRIVER, 'bool');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_MONGODB_ODM_DRIVER, 'bool');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_PHPCR_ODM_DRIVER, 'bool');

		$config['db_drivers'] = (object) $config['db_drivers'];

		Validators::assertField($config, 'event_listeners', 'array');
		Validators::assertField($config['event_listeners'], 'allow_all', 'bool');
		Validators::assertField($config['event_listeners'], 'excluded', 'string[]');

		$config['event_listeners'] = (object) $config['event_listeners'];

		return (object) $config;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getConfigFiles(): iterable
	{
		$files = [
			__DIR__ . '/../config/loader.neon',
			__DIR__ . '/../config/purge_mode.neon',
			__DIR__ . '/../config/event_manager.neon',
		];

		foreach ($this->getEnabledDrivers() as $name => $_) {
			$files[] = __DIR__ . '/../config/' . $name . '.neon';
		}

		return $files;
	}

	/**
	 * {@inheritDoc}
	 * @throws \LogicException
	 */
	protected function doLoadConfiguration(): void
	{
		$this->checkExtension(NelmioAliceExtension::class);

		$builder = $this->getContainerBuilder();

		$builder->addDefinition('fidry_alice_data_fixtures.logger')
			->setType(LoggerInterface::class)
			->setFactory(LoggerDecorator::class)
			->setAutowired(FALSE);

		$builder->addDefinition('fidry_alice_data_fixtures.driver_provider')
			->setType(IDriverProvider::class)
			->setFactory(DriverProvider::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		foreach ($builder->findByType(ProcessorInterface::class) as $definition) {
			$definition->addTag(self::TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR);
		}

		$loggerService = $builder->getByType(LoggerInterface::class, FALSE);

		if (NULL !== $loggerService) {
			$builder->getDefinition('fidry_alice_data_fixtures.logger')
				->setArguments([$builder->getDefinition($loggerService)]);
		}

		$this->setServiceArgument($builder->getDefinition('fidry_alice_data_fixtures.default_purge_mode'), $this->validConfig->default_purge_mode, 0);

		foreach ($this->getEnabledDrivers() as $alias) {
			$this->setFixturesProcessors('fidry_alice_data_fixtures.' . $alias . '.persister_loader');
		}

		$this->addServiceArguments(
			$builder->getDefinition('fidry_alice_data_fixtures.driver_provider'),
			NULL,
			$builder->findByType(IDriver::class),
			$this->validConfig->default_driver
		);

		$this->addServiceArguments(
			$builder->getDefinition('fidry_alice_data_fixtures.event_manager_restrictor_factory.default'),
			0,
			$this->validConfig->event_listeners->allow_all,
			$this->validConfig->event_listeners->excluded
		);
	}

	/**
	 * @param string $serviceName
	 * @param int    $argumentIndex
	 *
	 * @return void
	 */
	private function setFixturesProcessors(string $serviceName, int $argumentIndex = 3): void
	{
		$this->tryGetDefinition($serviceName, function ($definition) use ($argumentIndex) {
			$this->setServiceArgument($definition, $this->findServicesByTag(self::TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR), $argumentIndex);
		});
	}

	/**
	 * @param string        $name
	 * @param callable|NULL $cb
	 *
	 * @return \Nette\DI\Definitions\Definition|NULL
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	private function tryGetDefinition(string $name, ?callable $cb = NULL)
	{
		try {
			$definition = $this->getContainerBuilder()->getDefinition($name);

			if (is_callable($cb)) {
				$cb($definition);
			}

			return $definition;
		} catch (MissingServiceException $e) {
			return NULL;
		}
	}

	/**
	 * @return \Generator
	 */
	private function getEnabledDrivers(): Generator
	{
		$map = [
			self::DOCTRINE_ORM_DRIVER => 'doctrine',
			self::DOCTRINE_MONGODB_ODM_DRIVER => 'doctrine_mongodb',
			self::DOCTRINE_PHPCR_ODM_DRIVER => 'doctrine_phpcr',
		];

		foreach ($this->validConfig->db_drivers as $name => $enabled) {
			if (TRUE === $enabled) {
				yield $name => $map[$name];
			}
		}
	}
}
