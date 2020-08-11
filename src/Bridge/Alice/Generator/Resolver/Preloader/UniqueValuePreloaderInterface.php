<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader;

interface UniqueValuePreloaderInterface
{
	/**
	 * @param string $className
	 * @param string $column
	 *
	 * @return array
	 */
	public function preload(string $className, string $column): array;
}
