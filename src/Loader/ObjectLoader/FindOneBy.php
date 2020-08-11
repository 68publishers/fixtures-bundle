<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader;

final class FindOneBy extends AbstractDelegatingObjectLoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 * @param array                                                                            $criteria
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className, array $criteria)
	{
		parent::__construct($idGenerator, $className, $criteria);
	}
}
