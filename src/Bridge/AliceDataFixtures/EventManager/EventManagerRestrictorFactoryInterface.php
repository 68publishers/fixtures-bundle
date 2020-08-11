<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager;

use Doctrine\Common\EventManager;

interface EventManagerRestrictorFactoryInterface
{
	/**
	 * @param \Doctrine\Common\EventManager $eventManager
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager\EventManagerRestrictorInterface
	 */
	public function create(EventManager $eventManager): EventManagerRestrictorInterface;
}
