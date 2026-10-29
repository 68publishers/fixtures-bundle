<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\Console;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\FileExporterInterface;
use SixtyEightPublishers\FixturesBundle\Console\ListDataFixturesCommand;
use SixtyEightPublishers\FixturesBundle\Console\LoadDataFixturesCommand;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverProviderInterface;

require __DIR__ . '/../../bootstrap.php';

/**
 * Lazy command loaders (e.g. contributte/console 0.10+) need the names before the commands are created, they read them from #[AsCommand].
 */
final class CommandNameTest extends TestCase
{
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	public function testCommandsAreNamedByAttribute(): void
	{
		$load = new LoadDataFixturesCommand(
			Mockery::mock(ScenarioProviderInterface::class),
			Mockery::mock(DriverProviderInterface::class),
			new NamedPurgeMode('delete', PurgeMode::createDeleteMode())
		);
		$list = new ListDataFixturesCommand(
			Mockery::mock(ScenarioProviderInterface::class),
			Mockery::mock(FileResolverInterface::class),
			Mockery::mock(FileExporterInterface::class)
		);

		Assert::same('fixtures:load', $load->getName());
		Assert::same('Load data fixtures to your database.', $load->getDescription());
		Assert::same('fixtures:list', $list->getName());
		Assert::same('Show a list of available fixtures and scenarios.', $list->getDescription());
	}
}

(new CommandNameTest())->run();
