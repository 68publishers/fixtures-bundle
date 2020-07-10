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
use SixtyEightPublishers\FixturesBundle\Scenario\IScenario;
use SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider;

final class DataFixturesListCommand extends Command
{
	private const   FORMAT_TABLE = 'table',
					FORMAT_RAW = 'raw';

	private const FORMATS = [
		self::FORMAT_TABLE,
		self::FORMAT_RAW,
	];

	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider  */
	private $scenarioProvider;

	/** @var \Fidry\AliceDataFixtures\FileResolverInterface  */
	private $fileResolver;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider $scenarioProvider
	 * @param \Fidry\AliceDataFixtures\FileResolverInterface                  $fileResolver
	 */
	public function __construct(IScenarioProvider $scenarioProvider, FileResolverInterface $fileResolver)
	{
		parent::__construct();

		$this->scenarioProvider = $scenarioProvider;
		$this->fileResolver = $fileResolver;
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

		# print singe scenario
		if (NULL !== $scenario) {
			$this->printScenario($output, $this->scenarioProvider->getScenario($scenario), $format);

			return 0;
		}

		# or print all scenarios
		/** @var \SixtyEightPublishers\FixturesBundle\Scenario\IScenario $scenario */
		foreach ($this->scenarioProvider as $scenario) {
			$this->printScenario($output, $scenario, $format);
		}

		return 0;
	}

	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface       $output
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenario $scenario
	 * @param string                                                  $format
	 *
	 * @return void
	 */
	private function printScenario(OutputInterface $output, IScenario $scenario, string $format): void
	{
		$this->resolveFormat($format, [
			self::FORMAT_TABLE => function () use ($output, $scenario) {
				$table = new Table($output);

				$table->setHeaders([$scenario->getName()]);

				foreach ($this->fileResolver->resolve($scenario->getFixtures()) as $filename) {
					$table->addRow([$filename]);
				}

				$table->render();
			},
			self::FORMAT_RAW => function () use ($output, $scenario) {
				$output->writeln($scenario->getName());

				foreach ($this->fileResolver->resolve($scenario->getFixtures()) as $filename) {
					$output->writeln($filename);
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
