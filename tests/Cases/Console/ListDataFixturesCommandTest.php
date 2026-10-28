<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\Console;

use Mockery;
use ArrayIterator;
use Tester\Assert;
use Tester\TestCase;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Symfony\Component\Console\Tester\CommandTester;
use SixtyEightPublishers\FixturesBundle\FileExporterInterface;
use SixtyEightPublishers\FixturesBundle\Console\ListDataFixturesCommand;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface;

require __DIR__ . '/../../bootstrap.php';

final class ListDataFixturesCommandTest extends TestCase
{
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	public function testFormatOptionWithoutValueFallsBackToTable(): void
	{
		$scenarioProvider = Mockery::mock(ScenarioProviderInterface::class);
		$scenarioProvider->shouldReceive('getIterator')->andReturn(new ArrayIterator([]));

		$fileExporter = Mockery::mock(FileExporterInterface::class);
		$fileExporter->shouldReceive('export')->andReturn(['users.yaml']);

		$tester = new CommandTester(new ListDataFixturesCommand($scenarioProvider, Mockery::mock(FileResolverInterface::class), $fileExporter));

		Assert::same(0, $tester->execute(['--format' => NULL]));
		Assert::contains('All available fixtures', $tester->getDisplay());
		Assert::contains('| users.yaml', $tester->getDisplay());
	}
}

(new ListDataFixturesCommandTest())->run();
