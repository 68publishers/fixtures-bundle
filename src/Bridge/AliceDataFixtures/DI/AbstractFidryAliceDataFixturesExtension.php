<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI;

use LogicException;
use Psr\Log\LoggerInterface;
use Nette\DI\MissingServiceException;
use Fidry\AliceDataFixtures\ProcessorInterface;
use SixtyEightPublishers\FixturesBundle\Logger\LoggerDecorator;
use SixtyEightPublishers\FixturesBundle\Bridge\AbstractBridgeExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAlice24Extension;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\AbstractNelmioAliceExtension;

abstract class AbstractFidryAliceDataFixturesExtension extends AbstractBridgeExtension
{
	public const DOCTRINE_ORM_DRIVER = 'doctrine_orm';
	public const DOCTRINE_MONGODB_ODM_DRIVER = 'doctrine_mongodb_odm';
	public const DOCTRINE_PHPCR_ODM_DRIVER = 'doctrine_phpcr_odm';

	public const TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR = 'fidry_alice_data_fixtures.processor';

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
		if (0 >= count($this->compiler->getExtensions(AbstractNelmioAliceExtension::class))) {
			throw new LogicException(sprintf(
				'Cannot register "%s" without "%s".',
				static::class,
				static::class === FidryAliceDataFixturesExtension::class ? NelmioAliceExtension::class : NelmioAlice24Extension::class
			));
		}

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
			$this->setServiceArgument($definition, $this->getContainerBuilder()->findByTag(self::TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR), $argumentIndex);
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
