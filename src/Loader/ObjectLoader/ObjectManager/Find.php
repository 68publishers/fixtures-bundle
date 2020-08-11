<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectManager;

use Doctrine\Common\Persistence\ObjectManager;
use SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface;

final class Find extends ObjectManagerLoader
{
	/** @var mixed  */
	private $id;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                           $className
	 * @param mixed                                                                            $id
	 */
	public function __construct(IdGeneratorInterface $idGenerator, string $className, $id)
	{
		parent::__construct($idGenerator, $className);

		$this->id = $id;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doLoad(ObjectManager $manager): array
	{
		$object = $manager->getRepository($this->getClassName())->find($this->id);

		if (NULL === $object) {
			return [];
		}

		return $this->combineObjectsWithIds([$object]);
	}
}
