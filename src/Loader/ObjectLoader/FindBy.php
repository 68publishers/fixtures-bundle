<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader;

final class FindBy extends AbstractDelegatingObjectLoader
{
	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 * @param array                                                                            $criteria
	 * @param array|NULL                                                                       $orderBy
	 * @param int|NULL                                                                         $limit
	 * @param int|NULL                                                                         $offset
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className, array $criteria, ?array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
	{
		parent::__construct($idGenerator, $className, $criteria, $orderBy, $limit, $offset);
	}
}
