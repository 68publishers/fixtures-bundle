<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader;

interface ObjectLoaderInterface
{
	/**
	 * @param mixed $storageDriver
	 *
	 * @return array
	 */
	public function load($storageDriver): array;

	/**
	 * Returns FQN of loaded entities
	 *
	 * @return string
	 */
	public function getClassName(): string;
}
