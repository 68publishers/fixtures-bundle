<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI;

use Nette\Utils\Validators;
use Nette\Utils\AssertionException;

final class FidryAliceDataFixtures24Extension extends AbstractFidryAliceDataFixturesExtension
{
	/** @var array  */
	private $defaults = [
		'default_purge_mode' => 'delete', # 'delete', 'truncate', 'no_purge'
		'db_drivers' => [
			self::DOCTRINE_ORM_DRIVER => FALSE,
			self::DOCTRINE_MONGODB_ODM_DRIVER => FALSE,
			self::DOCTRINE_PHPCR_ODM_DRIVER => FALSE,
		],
	];

	/**
	 * {@inheritDoc}
	 * @throws \Nette\Utils\AssertionException
	 */
	protected function getValidConfig(): object
	{
		$config = $this->validateConfig($this->defaults);

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

		return (object) $config;
	}
}
