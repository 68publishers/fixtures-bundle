<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager;

interface EventManagerRestrictorInterface
{
	/**
	 * Modifies EventManager's listeners
	 *
	 * @return void
	 */
	public function restrict(): void;
}
