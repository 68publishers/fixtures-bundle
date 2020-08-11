<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface;

final class FindOneBy extends ObjectManagerLoader
{
	/** @var array  */
	private $criteria;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 * @param array                                                                            $criteria
	 */
	public function __construct(IdGeneratorInterface $idGenerator, string $className, array $criteria)
	{
		parent::__construct($idGenerator, $className);

		$this->criteria = $criteria;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doLoad(ObjectManager $manager): array
	{
		$object = $manager->getRepository($this->getClassName())->findOneBy($this->criteria);

		if (NULL === $object) {
			return [];
		}

		return $this->combineObjectsWithIds([$object]);
	}
}
