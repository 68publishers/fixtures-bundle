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
use SixtyEightPublishers\FixturesBundle\Loader\ILoader;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriverProvider;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Logger\LoggerDecorator;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;

final class LoadDataFixturesCommand extends Command
{
	/** @var \SixtyEightPublishers\FixturesBundle\Loader\ILoader  */
	private $loader;

	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider  */
	private $scenarioProvider;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriverProvider  */
	private $driverProvider;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode  */
	private $defaultPurgeMode;

	/** @var NULL|\Psr\Log\LoggerInterface  */
	private $logger;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ILoader                                      $loader
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\IScenarioProvider                          $scenarioProvider
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriverProvider     $driverProvider
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode $defaultPurgeMode
	 * @param \Psr\Log\LoggerInterface|NULL                                                            $logger
	 */
	public function __construct(ILoader $loader, IScenarioProvider $scenarioProvider, IDriverProvider $driverProvider, NamedPurgeMode $defaultPurgeMode, LoggerInterface $logger = NULL)
	{
		parent::__construct();

		$this->loader = $loader;
		$this->scenarioProvider = $scenarioProvider;
		$this->driverProvider = $driverProvider;
		$this->defaultPurgeMode = $defaultPurgeMode;
		$this->logger = $logger;
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

		$this->loader->load($this->driverProvider->getDriver($driver), $scenario);

		return 0;
	}
}
