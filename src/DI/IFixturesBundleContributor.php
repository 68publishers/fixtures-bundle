<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\DI;

interface IFixturesBundleContributor
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\DI\Configuration $configuration
	 */
	public function contributeToFixturesBundle(Configuration $configuration): void;
}
