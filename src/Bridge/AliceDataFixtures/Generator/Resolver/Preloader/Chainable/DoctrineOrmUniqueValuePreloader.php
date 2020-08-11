<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Generator\Resolver\Preloader\Chainable;

use Nelmio\Alice\IsAServiceTrait;
use Doctrine\ORM\EntityManagerInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Generator\Resolver\Preloader\ChainableUniqueValuePreloaderInterfaceInterface;

final class DoctrineOrmUniqueValuePreloader implements ChainableUniqueValuePreloaderInterfaceInterface
{
	use IsAServiceTrait;

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface $em
	 */
	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	/**
	 * {@inheritDoc}
	 */
	public function canPreload(LoadContext $context): bool
	{
		return DriverInterface::DOCTRINE_ORM_DRIVER === $context->getDriver();
	}

	/**
	 * {@inheritDoc}
	 */
	public function preload(string $className, string $column): array
	{
		$result = $this->em->createQueryBuilder()
			->select('p.' . $column)
			->from($className, 'p')
			->getQuery()
			->getScalarResult();

		return array_column($result, $column);
	}
}
