<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader;

final class FindAll extends AbstractDelegatingObjectLoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className)
	{
		parent::__construct($idGenerator, $className);
	}
}
