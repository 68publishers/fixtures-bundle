<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver;

use Fidry\AliceDataFixtures\LoaderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PersisterInterface;

interface DriverInterface extends LoaderInterface, PersisterInterface
{
	public const DOCTRINE_ORM_DRIVER = 'doctrine_orm';
	public const DOCTRINE_MONGODB_ODM_DRIVER = 'doctrine_mongodb_odm';
	public const DOCTRINE_PHPCR_ODM_DRIVER = 'doctrine_phpcr_odm';

	/**
	 * @return string
	 */
	public function getName(): string;
}
