<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\Id;

interface IdGeneratorInterface
{
	/**
	 * @return string
	 */
	public function generate(): string;
}
