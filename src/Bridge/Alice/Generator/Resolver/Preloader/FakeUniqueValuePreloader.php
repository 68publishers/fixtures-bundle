<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader;

use Nelmio\Alice\IsAServiceTrait;

final class FakeUniqueValuePreloader implements UniqueValuePreloaderInterface
{
	use IsAServiceTrait;

	/**
	 * {@inheritDoc}
	 */
	public function preload(string $className, string $column): array
	{
		return [];
	}
}
