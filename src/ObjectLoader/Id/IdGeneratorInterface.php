<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\Id;

interface IdGeneratorInterface
{
	/**
	 * @return string
	 */
	public function generate(): string;

	/**
	 * Resets the generator e.g. set a sequence again to the default value
	 */
	public function reset(): void;
}
