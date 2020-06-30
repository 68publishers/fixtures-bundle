<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Nette;

interface IFixturesBundleContributor
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Nette\Configuration $configuration
	 */
	public function contributeToFixturesBundle(Configuration $configuration): void;
}
