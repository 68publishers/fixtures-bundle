<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Helper;

use Tester\FileMock;
use Nette\DI\Compiler;
use Nette\StaticClass;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension;

final class ContainerFactory
{
	use StaticClass;

	/**
	 * @param string       $name
	 * @param string|array $config
	 *
	 * @return \Nette\DI\Container
	 */
	public static function createContainer(string $name, $config): Container
	{
		$loader = new ContainerLoader(TEMP_PATH . '/Nette.Configurator_' . md5($name), TRUE);
		$class = $loader->load(static function (Compiler $compiler) use ($config): void {
			$compiler->addExtension('nelmio_alice', new NelmioAliceExtension());

			if (is_array($config)) {
				$compiler->addConfig($config);
			} elseif (is_file($config)) {
				$compiler->loadConfig($config);
			} else {
				$compiler->loadConfig(FileMock::create((string) $config, 'neon'));
			}
		}, $name);

		return new $class();
	}
}
