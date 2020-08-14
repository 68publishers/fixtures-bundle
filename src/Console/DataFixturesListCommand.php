<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Console;

use RuntimeException;
use InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Fidry\AliceDataFixtures\FileResolverInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixtyEightPublishers\FixturesBundle\FileExporterInterface;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface;

final class DataFixturesListCommand extends Command
{
	private const   FORMAT_TABLE = 'table',
					FORMAT_RAW = 'raw';

	private const FORMATS = [
		self::FORMAT_TABLE,
		self::FORMAT_RAW,
	];

	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface  */
	private $scenarioProvider;

	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/** @var \SixtyEightPublishers\FixturesBundle\FileExporterInterface  */
	private $fileExporter;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface $scenarioProvider
	 * @param \Fidry\AliceDataFixtures\FileResolverInterface                          $fileResolver
	 * @param \SixtyEightPublishers\FixturesBundle\FileExporterInterface              $fileExporter
	 */
	public function __construct(ScenarioProviderInterface $scenarioProvider, FileResolverInterface $fileResolver, FileExporterInterface $fileExporter)
	{
		parent::__construct();

		$this->scenarioProvider = $scenarioProvider;
		$this->fileResolver = $fileResolver;
		$this->fileExporter = $fileExporter;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('fixtures:list')
			->setDescription('Show a list of available fixtures and scenarios.')
			->addArgument(
				'scenario',
				InputArgument::OPTIONAL,
				'The name of a scenario.'
			)
			->addOption(
				'format',
				'f',
				InputOption::VALUE_OPTIONAL,
				'The format of an output. Allowed values are "table" or "raw".',
				self::FORMAT_TABLE
			);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws RuntimeException Unsupported Application type
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$scenario = $input->getArgument('scenario');
		$format = $input->getOption('format');

		# a simple check of a format's value
		$this->resolveFormat($format, [
			self::FORMAT_TABLE => TRUE,
			self::FORMAT_RAW => TRUE,
		]);

		# print single scenario
		if (NULL !== $scenario) {
			$this->printScenario($output, $this->scenarioProvider->getScenario($scenario), $format);

			return 0;
		}

		# or print all available fixtures and all scenarios
		$this->printFixturesList($output, $format);

		/** @var \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface $scenario */
		foreach ($this->scenarioProvider as $scenario) {
			$this->printScenario($output, $scenario, $format);
		}

		return 0;
	}

	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @param string                                            $format
	 *
	 * @return void
	 */
	private function printFixturesList(OutputInterface $output, string $format): void
	{
		$files = $this->fileExporter->export();

		$this->resolveFormat($format, [
			self::FORMAT_TABLE => static function () use ($output, $files) {
				(new Table($output))
					->setHeaders(['All available fixtures'])
					->setRows(array_map(static function (string $file) {
						return [$file];
					}, $files))
					->render();
			},
			self::FORMAT_RAW => static function () use ($output, $files) {
				$output->writeln('All available fixtures:');

				foreach ($files as $file) {
					$output->writeln($file);
				}

				$output->writeln('');
			},
		]);
	}

	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface               $output
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioInterface $scenario
	 * @param string                                                          $format
	 *
	 * @return void
	 */
	private function printScenario(OutputInterface $output, ScenarioInterface $scenario, string $format): void
	{
		$output->writeln('Scenario ' . $scenario->getName() . ':');

		$this->resolveFormat($format, [
			self::FORMAT_TABLE => function () use ($output, $scenario) {
				$table = new Table($output);

				$table->setHeaders(['Scene', 'Files']);

				/** @var \SixtyEightPublishers\FixturesBundle\Scenario\Scene\Scene $scene */
				foreach ($scenario as $scene) {
					$table->addRow([
						$scene->getName(),
						implode("\n", $this->fileResolver->resolve($scene->getFixtures())),
					]);
				}

				$table->render();
			},
			self::FORMAT_RAW => function () use ($output, $scenario) {
				/** @var \SixtyEightPublishers\FixturesBundle\Scenario\Scene\Scene $scene */
				foreach ($scenario as $scene) {
					$output->writeln('Scene ' . $scene->getName() . ':');

					foreach ($this->fileResolver->resolve($scene->getFixtures()) as $filename) {
						$output->writeln($filename);
					}
				}

				$output->writeln('');
			},
		]);
	}

	/**
	 * @param string $format
	 * @param array  $callbacks
	 *
	 * @return mixed
	 */
	private function resolveFormat(string $format, array $callbacks)
	{
		if (isset($callbacks[$format])) {
			$cb = $callbacks[$format];

			return is_callable($cb) ? $cb() : $cb;
		}

		throw new InvalidArgumentException(sprintf(
			'Unsupported format "%s". Valid values are [%s].',
			$format,
			implode(', ', self::FORMATS)
		));
	}
}
