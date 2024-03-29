<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectManager;

use InvalidArgumentException;
use Doctrine\Persistence\ObjectManager;
use SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectLoaderInterface;
use SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface;

abstract class ObjectManagerLoader implements ObjectLoaderInterface
{
	/** @var \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface  */
	protected $idGenerator;

	/** @var string  */
	protected $className;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                    $className
	 */
	public function __construct(IdGeneratorInterface $idGenerator, string $className)
	{
		$this->idGenerator = $idGenerator;
		$this->className = $className;
	}

	/**
	 * @param \Doctrine\Persistence\ObjectManager $manager
	 *
	 * @return array
	 */
	abstract protected function doLoad(ObjectManager $manager): array;

	/**
	 * {@inheritDoc}
	 *
	 * @throws \InvalidArgumentException
	 */
	public function load($storageDriver): array
	{
		if (!$storageDriver instanceof ObjectManager) {
			throw new InvalidArgumentException(sprintf(
				'A storage driver must be instance of %s',
				ObjectManager::class
			));
		}

		return $this->doLoad($storageDriver);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	/**
	 * @param array $objects
	 *
	 * @return array
	 */
	protected function combineObjectsWithIds(array $objects): array
	{
		$result = [];

		foreach ($objects as $object) {
			$result[$this->idGenerator->generate()] = $object;
		}

		return $result;
	}
}
