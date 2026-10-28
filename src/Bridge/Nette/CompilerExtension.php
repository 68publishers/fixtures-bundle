<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Nette;

use LogicException;
use Nette\Utils\Finder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\CompilerExtension as NetteCompilerExtension;

abstract class CompilerExtension extends NetteCompilerExtension
{
	/** @var object|NULL */
	protected $validConfig;

	/**
	 * Use instead of ::loadConfiguration()
	 *
	 * @return void
	 */
	protected function doLoadConfiguration(): void
	{
	}

	/**
	 * {@inheritDoc}
	 * @throws \ReflectionException
	 */
	public function loadConfiguration(): void
	{
		$this->validConfig = $this->config;

		$this->doLoadConfiguration();
		$this->loadNeonConfiguration();
	}

	/**
	 * @return iterable
	 * @throws \ReflectionException
	 */
	protected function getConfigFiles(): iterable
	{
		$dir = dirname((new \ReflectionClass($this))->getFileName()) . '/../config';

		return is_dir($dir) ? array_keys(iterator_to_array(Finder::findFiles('*.neon')->from($dir))) : [];
	}

	/**
	 * @return void
	 * @throws \ReflectionException
	 */
	protected function loadNeonConfiguration(): void
	{
		foreach ($this->getConfigFiles() as $filename) {
			$this->compiler->loadDefinitionsFromConfig($this->loadFromFile($filename)['services']);
		}
	}

	/**
	 * @param string $tag
	 *
	 * @return \Nette\DI\Definitions\ServiceDefinition[]
	 */
	protected function findServicesByTag(string $tag): array
	{
		$builder = $this->getContainerBuilder();

		return array_map(static function ($name) use ($builder) {
			return $builder->getDefinition($name);
		}, array_keys($builder->findByTag($tag)));
	}

	/**
	 * @param \Nette\DI\Definitions\ServiceDefinition|\Nette\DI\Definitions\Definition $definition
	 * @param int|NULL                                                                 $index
	 * @param mixed                                                                    ...$args
	 *
	 * @return void
	 */
	protected function addServiceArguments(ServiceDefinition $definition, ?int $index = NULL, ...$args): void
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
	 * @param \Nette\DI\Definitions\ServiceDefinition|\Nette\DI\Definitions\Definition $definition
	 * @param mixed                                                                    $arg
	 * @param int                                                                      $index
	 *
	 * @return void
	 */
	protected function setServiceArgument(ServiceDefinition $definition, $arg, int $index): void
	{
		$args = NULL !== $definition->getFactory() ? $definition->getFactory()->arguments : [];
		$keys = array_keys($args);

		$args[$keys[$index] ?? $index] = $arg;
		ksort($args);

		$definition->setArguments($args);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 * @throws \LogicException
	 */
	protected function checkExtension(string $name): bool
	{
		if (0 >= count($this->compiler->getExtensions($name))) {
			throw new LogicException(sprintf(
				'Cannot register "%s" without "%s".',
				static::class,
				$name
			));
		}

		return TRUE;
	}
}
