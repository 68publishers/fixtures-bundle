<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI;

use Nette\Utils\Validators;
use Nette\Utils\AssertionException;

final class NelmioAlice24Extension extends AbstractNelmioAliceExtension
{
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
	 * @throws \Nette\Utils\AssertionException
	 */
	protected function getValidConfig(): object
	{
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'locale', 'string');
		Validators::assertField($config, 'seed', 'int|null');

		if (is_int($config['seed']) && 1 > $config['seed']) {
			throw new AssertionException('The value of an option "seed" must be a null or an integer higher than 0.');
		}

		Validators::assertField($config, 'functions_blacklist', 'string[]');
		Validators::assertField($config, 'loading_limit', 'int');
		Validators::assertField($config, 'max_unique_values_retry', 'int');

		if (1 > $config['loading_limit']) {
			throw new AssertionException('The value of an option "loading_limit" must be an integer higher than 0.');
		}

		if (1 > $config['max_unique_values_retry']) {
			throw new AssertionException('The value of an option "max_unique_values_retry" must be an integer higher than 0.');
		}

		return (object) $config;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function loadDefinitions(array $services): void
	{
		$this->compiler::loadDefinitions($this->getContainerBuilder(), $services);
	}
}
