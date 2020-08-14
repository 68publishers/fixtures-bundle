<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader;

final class Find extends AbstractDelegatingObjectLoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                    $className
	 * @param $id
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className, $id)
	{
		parent::__construct($idGenerator, $className, $id);
	}
}
