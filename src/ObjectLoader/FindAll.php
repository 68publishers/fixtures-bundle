<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader;

final class FindAll extends AbstractDelegatingObjectLoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                    $className
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className)
	{
		parent::__construct($idGenerator, $className);
	}
}
