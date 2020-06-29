<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Validators;
use Psr\Log\LoggerInterface;
use Nette\Utils\AssertionException;
use Nette\DI\MissingServiceException;
use Fidry\AliceDataFixtures\ProcessorInterface;
use SixtyEightPublishers\FixturesBundle\Logger\LoggerDecorator;
use SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;

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
			'default_purge_mode' => Expect::anyOf('delete', 'truncate', 'no_purge')->default('delete')->dynamic(),
			'db_drivers' => Expect::structure([
				self::DOCTRINE_ORM_DRIVER => Expect::bool(FALSE),
				self::DOCTRINE_MONGODB_ODM_DRIVER => Expect::bool(FALSE),
				self::DOCTRINE_PHPCR_ODM_DRIVER => Expect::bool(FALSE),
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
			'default_purge_mode' => 'delete', # 'delete', 'truncate', 'no_purge'
			'db_drivers' => [
				self::DOCTRINE_ORM_DRIVER => FALSE,
				self::DOCTRINE_MONGODB_ODM_DRIVER => FALSE,
				self::DOCTRINE_PHPCR_ODM_DRIVER => FALSE,
			],
		]);

		Validators::assertField($config, 'default_purge_mode', 'string');

		if (!in_array($config['default_purge_mode'], ['delete', 'truncate', 'no_purge'], TRUE)) {
			throw new AssertionException(sprintf(
				'Invalid purge mode %s. Choose either "delete", "truncate" or "no_purge".',
				$config['default_purge_mode']
			));
		}

		Validators::assertField($config, 'db_drivers', 'array');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_ORM_DRIVER, 'bool');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_MONGODB_ODM_DRIVER, 'bool');
		Validators::assertField($config['db_drivers'], self::DOCTRINE_PHPCR_ODM_DRIVER, 'bool');

		$config['db_drivers'] = (object) $config['db_drivers'];

		return (object) $config;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getConfigFiles(): iterable
	{
		$files = [
			__DIR__ . '/../config/loader.neon',
		];

		foreach ($this->validConfig->db_drivers as $name => $enabled) {
			if (TRUE === $enabled) {
				$files[] = __DIR__ . '/../config/' . $name . '.neon';
			}
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
				->setArguments([$loggerService]);
		}

		if ($this->validConfig->db_drivers->{self::DOCTRINE_ORM_DRIVER}) {
			$this->setDefaultPurgeMode('fidry_alice_data_fixtures.loader.doctrine');
			$this->setFixturesProcessors('fidry_alice_data_fixtures.doctrine.persister_loader');
		}

		if ($this->validConfig->db_drivers->{self::DOCTRINE_MONGODB_ODM_DRIVER}) {
			$this->setDefaultPurgeMode('fidry_alice_data_fixtures.loader.doctrine_mongodb');
			$this->setFixturesProcessors('fidry_alice_data_fixtures.doctrine_mongodb.persister_loader');
		}

		if ($this->validConfig->db_drivers->{self::DOCTRINE_PHPCR_ODM_DRIVER}) {
			$this->setDefaultPurgeMode('fidry_alice_data_fixtures.loader.doctrine_phpcr');
			$this->setFixturesProcessors('fidry_alice_data_fixtures.doctrine_phpcr.persister_loader');
		}
	}

	/**
	 * @param string $serviceName
	 * @param int    $argumentIndex
	 *
	 * @return void
	 */
	private function setDefaultPurgeMode(string $serviceName, int $argumentIndex = 2): void
	{
		$this->tryGetDefinition($serviceName, function ($definition) use ($argumentIndex) {
			$this->setServiceArgument($definition, $this->validConfig->default_purge_mode, $argumentIndex);
		});
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
}
