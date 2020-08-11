<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface;

final class FindBy extends ObjectManagerLoader
{
	/** @var array  */
	private $criteria;

	/** @var array|NULL  */
	private $orderBy;

	/** @var int|NULL  */
	private $limit;

	/** @var int|NULL  */
	private $offset;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 * @param array                                                                            $criteria
	 * @param array|NULL                                                                       $orderBy
	 * @param int|NULL                                                                         $limit
	 * @param int|NULL                                                                         $offset
	 */
	public function __construct(IdGeneratorInterface $idGenerator, string $className, array $criteria, ?array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
	{
		parent::__construct($idGenerator, $className);

		$this->criteria = $criteria;
		$this->orderBy = $orderBy;
		$this->limit = $limit;
		$this->offset = $offset;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doLoad(ObjectManager $manager): array
	{
		return $this->combineObjectsWithIds(
			$manager->getRepository($this->getClassName())->findBy($this->criteria, $this->orderBy, $this->limit, $this->offset)
		);
	}
}
