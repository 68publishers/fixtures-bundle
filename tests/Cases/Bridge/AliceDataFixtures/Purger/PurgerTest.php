<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\Bridge\AliceDataFixtures\Purger;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Purger\Purger;

require __DIR__ . '/../../../../bootstrap.php';

final class PurgerTest extends TestCase
{
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	public function testForeignKeyChecksAreDisabledOnMySqlPlatform(): void
	{
		foreach ([PurgeMode::createDeleteMode(), PurgeMode::createTruncateMode(), NULL] as $purgeMode) {
			$statements = [];
			$connection = $this->createConnection(new MySQLPlatform(), $statements);

			$this->createPurger($connection, $purgeMode)->purge();

			Assert::same(['SET FOREIGN_KEY_CHECKS = 0;', 'SET FOREIGN_KEY_CHECKS = 1;'], $statements);
		}
	}

	public function testForeignKeyChecksAreRestoredWhenPurgeFails(): void
	{
		$statements = [];
		$connection = $this->createConnection(new MySQLPlatform(), $statements);
		$purger = $this->createPurger($connection, PurgeMode::createDeleteMode(), new RuntimeException('Purge failed.'));

		Assert::exception(static fn () => $purger->purge(), RuntimeException::class, 'Purge failed.');
		Assert::same(['SET FOREIGN_KEY_CHECKS = 0;', 'SET FOREIGN_KEY_CHECKS = 1;'], $statements);
	}

	public function testForeignKeyChecksAreNotTouchedOnOtherPlatforms(): void
	{
		$statements = [];
		$connection = $this->createConnection(Mockery::mock(AbstractPlatform::class), $statements);

		$this->createPurger($connection, PurgeMode::createDeleteMode())->purge();

		Assert::same([], $statements);
	}

	private function createConnection(AbstractPlatform $platform, array &$statements): Connection
	{
		$connection = Mockery::mock(Connection::class);

		$connection->shouldReceive('getDatabasePlatform')->andReturn($platform);
		$connection->shouldReceive('getConfiguration')->andReturn(new Configuration());
		$connection->shouldReceive('executeStatement')->andReturnUsing(static function (string $sql) use (&$statements): int {
			$statements[] = $sql;

			return 0;
		});

		return $connection;
	}

	private function createPurger(Connection $connection, ?PurgeMode $purgeMode, ?RuntimeException $metadataError = NULL): Purger
	{
		# no entities, the real ORMPurger only reads the metadata and the platform
		$metadataFactory = Mockery::mock(ClassMetadataFactory::class);
		$getAllMetadata = $metadataFactory->shouldReceive('getAllMetadata')->once();

		if (NULL === $metadataError) {
			$getAllMetadata->andReturn([]);
		} else {
			$getAllMetadata->andThrow($metadataError);
		}

		$em = Mockery::mock(EntityManagerInterface::class);
		$em->shouldReceive('getConnection')->andReturn($connection);
		$em->shouldReceive('getMetadataFactory')->andReturn($metadataFactory);

		return new Purger($em, $purgeMode);
	}
}

(new PurgerTest())->run();
