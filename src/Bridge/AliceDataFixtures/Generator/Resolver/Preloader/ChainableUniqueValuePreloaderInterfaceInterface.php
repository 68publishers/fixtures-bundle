<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Generator\Resolver\Preloader;

use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\UniqueValuePreloaderInterface;

interface ChainableUniqueValuePreloaderInterfaceInterface extends UniqueValuePreloaderInterface
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext $context
	 *
	 * @return bool
	 */
	public function canPreload(LoadContext $context): bool;
}
