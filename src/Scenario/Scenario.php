<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use ArrayIterator;
use Psr\Log\LoggerInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;

final class Scenario implements ScenarioInterface
{
	/** @var string  */
	private $name;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|string|NULL  */
	private $purgeMode;

	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\Scene\SceneInterface[] */
	private $scenes;

	/**
	 * @param string      $name
	 * @param string|NULL $purgeMode
	 * @param \SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectLoaderInterface[]
	 */
	public function __construct(string $name, ?string $purgeMode, array $scenes = [])
	{
		$this->name = $name;
		$this->purgeMode = $purgeMode;
		$this->scenes = (static function (Scene\SceneInterface ...$scenes) {
			return $scenes;
		})(...$scenes);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurgeMode(): ?NamedPurgeMode
	{
		if (NULL === $this->purgeMode) {
			return NULL;
		}

		if (!$this->purgeMode instanceof NamedPurgeMode) {
			$this->purgeMode = PurgeModeFactory::create($this->purgeMode);
		}

		return $this->purgeMode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPurgeMode(?NamedPurgeMode $purgeMode): void
	{
		$this->purgeMode = $purgeMode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(DriverInterface $driver, LoggerInterface $logger, array $parameters = []): void
	{
		$logger->info(sprintf(
			'Running scenario "%s":',
			$this->getName()
		));

		$purgeMode = $this->getPurgeMode();

		if (NULL !== $purgeMode) {
			$purgeMode = $purgeMode->getMode();
		}

		foreach ($this->scenes as $scene) {
			$logger->info(sprintf(
				'Running scene "%s":',
				$scene->getName()
			));

			$scene->run($driver, $logger, $purgeMode, $parameters);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->scenes);
	}
}
