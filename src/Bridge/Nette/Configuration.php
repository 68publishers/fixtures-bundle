<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Nette;

use InvalidArgumentException;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Fidry\AliceDataFixtures\ProcessorInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI\FidryAliceDataFixturesExtension;

final class Configuration
{
	/** @var \Nette\DI\ContainerBuilder  */
	private $builder;

	/** @var string  */
	private $name;

	/** @var array */
	private $counters = [
		'faker_provider' => 0,
		'processor' => 0,
	];

	/** @var string|NULL */
	private $bundleName;

	/** @var string|NULL  */
	private $fixtureDir;

	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 * @param string                     $name
	 */
	public function __construct(ContainerBuilder $builder, string $name)
	{
		$this->builder = $builder;
		$this->name = $name;
	}

	/**
	 * @param mixed $factory
	 *
	 * @return \Nette\DI\Definitions\ServiceDefinition
	 */
	public function addFakerProvider($factory): ServiceDefinition
	{
		return $this->builder->addDefinition($this->prefix('faker_provider_' . $this->counters['faker_provider']++))
			->setFactory($factory)
			->addTag(NelmioAliceExtension::TAG_NELMIO_ALICE_FAKER_PROVIDER)
			->setAutowired(FALSE);
	}

	/**
	 * @param mixed $factory
	 *
	 * @return \Nette\DI\Definitions\ServiceDefinition
	 */
	public function addProcessor($factory): ServiceDefinition
	{
		return $this->builder->addDefinition($this->prefix('processor_' . $this->counters['processor']++))
			->setType(ProcessorInterface::class)
			->setFactory($factory)
			->addTag(FidryAliceDataFixturesExtension::TAG_FIDRY_ALICE_DATA_FIXTURES_PROCESSOR)
			->setAutowired(FALSE);
	}

	/**
	 * @param string $bundleName
	 * @param string $dir
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function provideFixtures(string $bundleName, string $dir): void
	{
		if (strpos($bundleName, '/')) {
			throw new InvalidArgumentException(sprintf('Bundle name can\'t contain a character "/".'));
		}

		if (!is_dir($dir)) {
			throw new InvalidArgumentException(sprintf(
				'Missing directory %s',
				$dir
			));
		}

		$this->bundleName = $bundleName;
		$this->fixtureDir = realpath($dir);
	}

	/**
	 * @internal
	 *
	 * @return string|NULL
	 */
	public function getBundleName(): ?string
	{
		return $this->bundleName;
	}

	/**
	 * @internal
	 *
	 * @return string|NULL
	 */
	public function getFixtureDir(): ?string
	{
		return $this->fixtureDir;
	}

	/**
	 * From Nette CompilerExtension
	 * @param string $id
	 *
	 * @return string
	 */
	private function prefix(string $id): string
	{
		return substr_replace($id, $this->name . '.', substr($id, 0, 1) === '@' ? 1 : 0, 0);
	}
}
