<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge;

use Nette\Utils\Finder;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;

require_once 'nette.di.compatibility.php';

abstract class AbstractBridgeExtension extends CompilerExtension
{
	/** @var object|NULL */
	protected $validConfig;

	/**
	 * @return object
	 */
	abstract protected function getValidConfig(): object;

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
		$this->validConfig = $this->getValidConfig();

		$this->doLoadConfiguration();
		$this->loadNeonConfiguration();
	}

	/**
	 * @return iterable
	 * @throws \ReflectionException
	 */
	protected function getConfigFiles(): iterable
	{
		return array_keys(iterator_to_array(Finder::findFiles('*.neon')->from(dirname((new \ReflectionClass($this))->getFileName()) . '/../config')));
	}

	/**
	 * @return void
	 * @throws \ReflectionException
	 */
	protected function loadNeonConfiguration(): void
	{
		$compiler = $this->compiler;

		foreach ($this->getConfigFiles() as $filename) {
			$services = $this->loadFromFile($filename)['services'];

			if (is_callable([$compiler, 'loadDefinitionsFromConfig'])) {
				$this->compiler->loadDefinitionsFromConfig($services);
			} else {
				$this->compiler::loadDefinitions($this->getContainerBuilder(), $services);
			}
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
}
