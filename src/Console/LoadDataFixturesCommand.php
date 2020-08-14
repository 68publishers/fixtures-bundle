<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Console;

use RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Logger\LoggerDecorator;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverProviderInterface;

final class LoadDataFixturesCommand extends Command
{
	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface  */
	private $scenarioProvider;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverProviderInterface  */
	private $driverProvider;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode  */
	private $defaultPurgeMode;

	/** @var NULL|\Psr\Log\LoggerInterface  */
	private $logger;

	/** @var array  */
	private $parameters = [];

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\ScenarioProviderInterface                      $scenarioProvider
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverProviderInterface $driverProvider
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode     $defaultPurgeMode
	 * @param \Psr\Log\LoggerInterface|NULL                                                                $logger
	 */
	public function __construct(ScenarioProviderInterface $scenarioProvider, DriverProviderInterface $driverProvider, NamedPurgeMode $defaultPurgeMode, LoggerInterface $logger = NULL)
	{
		parent::__construct();

		$this->scenarioProvider = $scenarioProvider;
		$this->driverProvider = $driverProvider;
		$this->defaultPurgeMode = $defaultPurgeMode;
		$this->logger = $logger;
	}

	/**
	 * @param array $parameters
	 *
	 * @return void
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('fixtures:load')
			->setDescription('Load data fixtures to your database.')
			->addArgument(
				'scenario',
				InputArgument::OPTIONAL,
				'The name of a scenario.',
				'default'
			)
			->addOption(
				'purge-mode',
				'p',
				InputOption::VALUE_OPTIONAL,
				'The purge mode to use for this scenario. If not specified, the purge mode from configuration or the default purge mode will be used.'
			)
			->addOption(
				'driver',
				'd',
				InputOption::VALUE_OPTIONAL,
				'The driver to use for this scenario. The default driver will be used if not specified.'
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
		$purgeMode = $input->getOption('purge-mode');
		$driver = $input->getOption('driver');

		$scenario = $this->scenarioProvider->getScenario($scenario);

		if (NULL !== $purgeMode) {
			$scenario->setPurgeMode(PurgeModeFactory::create($purgeMode));
		}

		if (NULL === $scenario->getPurgeMode()) {
			$scenario->setPurgeMode($this->defaultPurgeMode);
		}

		if (PurgeModeFactory::PURGE_MODE_NO_PURGE !== $scenario->getPurgeMode()->getName() && $input->isInteractive()) {
			/** @var QuestionHelper $questionHelper */
			$questionHelper = $this->getHelperSet()->get('question');
			$question = new ConfirmationQuestion('<question>Careful, database will be purged. Do you want to continue y/N ?</question>', FALSE);

			if (FALSE === ((bool) $questionHelper->ask($input, $output, $question))) {
				return 0;
			}
		}

		if ($this->logger instanceof LoggerDecorator && !$this->logger->hasLogger()) {
			$this->logger->setLogger(new ConsoleLogger($output));
		}

		$scenario->run($this->driverProvider->getDriver($driver), $this->logger ?? new ConsoleLogger($output), $this->parameters);

		return 0;
	}
}
