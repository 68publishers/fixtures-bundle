<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;

final class FindAll extends ObjectManagerLoader
{
	/**
	 * {@inheritDoc}
	 */
	protected function doLoad(ObjectManager $manager): array
	{
		return $this->combineObjectsWithIds(
			$manager->getRepository($this->getClassName())->findAll()
		);
	}
}
