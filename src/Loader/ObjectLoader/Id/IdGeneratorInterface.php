<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id;

interface IdGeneratorInterface
{
	/**
	 * @return string
	 */
	public function generate(): string;
}
