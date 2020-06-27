<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class FidryAliceDataFixturesExtension extends AbstractFidryAliceDataFixturesExtension
{
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
	 */
	protected function getValidConfig(): object
	{
		return $this->config;
	}
}
