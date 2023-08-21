<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Purger;

use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Doctrine\ODM\PHPCR\DocumentManager as DoctrinePhpCrDocumentManager;
use Doctrine\ODM\MongoDB\DocumentManager as DoctrineMongoDocumentManager;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger as DoctrinePhpCrPurger;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger as DoctrineMongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface as DoctrinePurgerInterface;

class Purger implements PurgerInterface, PurgerFactoryInterface
{
	use IsAServiceTrait;

	/** @var \Doctrine\Persistence\ObjectManager  */
	protected $manager;

	/** @var \Fidry\AliceDataFixtures\Persistence\PurgeMode  */
	protected $purgeMode;

	/** @var array  */
	protected $excluded;

	/** @var \Doctrine\Common\DataFixtures\Purger\PurgerInterface  */
	private $purger;

	/**
	 * @param \Doctrine\Persistence\ObjectManager          $manager
	 * @param \Fidry\AliceDataFixtures\Persistence\PurgeMode|NULL $purgeMode
	 * @param array                                               $excluded
	 */
	public function __construct(ObjectManager $manager, PurgeMode $purgeMode = NULL, array $excluded = [])
	{
		$this->manager = $manager;
		$this->purgeMode = $purgeMode;
		$this->excluded = $this->getExcludedTables($excluded);

		$this->purger = $this->createPurger();
	}

	/**
	 * Ignore the second argument - its totally useless because PurgerInterface contains the only method `::purge()`, nothing like `::getObjectManager()` etc.
	 *
	 * {@inheritDoc}
	 */
	public function create(PurgeMode $mode, PurgerInterface $purger = NULL): PurgerInterface
	{
		return new static($this->manager, $mode, $this->excluded);
	}

	/**
	 * Method has been copied from Fidry\AliceDataFixtures\Bridge\Doctrine\Purger\Purger
	 *
	 * {@inheritDoc}
	 */
	public function purge(): void
	{
		// Because MySQL rocks, you got to disable foreign key checks when doing a TRUNCATE/DELETE unlike in for example
		// PostgreSQL. This ideally should be done in the Purger of doctrine/data-fixtures but meanwhile we are doing
		// it here.
		// See the progress in https://github.com/doctrine/data-fixtures/pull/272
		$disableFkChecks = (
			$this->purger instanceof DoctrineOrmPurger
			&& in_array($this->purgeMode->getValue(), [PurgeMode::createDeleteMode()->getValue(), PurgeMode::createTruncateMode()->getValue()], TRUE)
			&& $this->purger->getObjectManager()->getConnection()->getDriver() instanceof AbstractMySQLDriver
		);

		if ($disableFkChecks) {
			$connection = $this->purger->getObjectManager()->getConnection();

			$connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
		}

		$this->purger->purge();

		if ($disableFkChecks && isset($connection)) {
			$connection->exec('SET FOREIGN_KEY_CHECKS = 1;');
		}
	}

	/**
	 * @return \Doctrine\Common\DataFixtures\Purger\PurgerInterface
	 */
	protected function createPurger(): DoctrinePurgerInterface
	{
		if ($this->manager instanceof EntityManagerInterface) {
			$purger = new DoctrineOrmPurger($this->manager, $this->excluded);

			if (NULL !== $this->purgeMode) {
				$purger->setPurgeMode($this->purgeMode->getValue());
			}

			return $purger;
		}

		if ($this->manager instanceof DoctrinePhpCrDocumentManager) {
			return new DoctrinePhpCrPurger($this->manager);
		}

		if ($this->manager instanceof DoctrineMongoDocumentManager) {
			return new DoctrineMongoDBPurger($this->manager);
		}

		throw new InvalidArgumentException(sprintf(
			'Cannot create a purger for ObjectManager of class %s',
			get_class($this->manager)
		));
	}

	/**
	 * @param array $excluded
	 *
	 * @return array
	 */
	private function getExcludedTables(array $excluded): array
	{
		# excluded tables are supported only by ORM
		if (!$this->manager instanceof EntityManagerInterface) {
			return [];
		}

		foreach ($excluded as $k => $v) {
			if (!class_exists($v) && !interface_exists($v)) {
				continue;
			}

			$excluded[$k] = $this->manager->getClassMetadata($v)->getTableName();
		}

		return $excluded;
	}
}
