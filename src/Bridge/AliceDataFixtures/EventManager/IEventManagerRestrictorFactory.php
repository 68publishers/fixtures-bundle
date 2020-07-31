<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager;

use Doctrine\Common\EventManager;

interface IEventManagerRestrictorFactory
{
	/**
	 * @param \Doctrine\Common\EventManager $eventManager
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager\IEventManagerRestrictor
	 */
	public function create(EventManager $eventManager): IEventManagerRestrictor;
}
