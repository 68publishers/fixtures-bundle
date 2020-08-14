<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario\Scene;

use Psr\Log\LoggerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\DriverInterface;

final class BatchedScene implements SceneInterface
{
	/** @var int  */
	private $batch;

	/** @var \SixtyEightPublishers\FixturesBundle\Scenario\Scene\SceneInterface  */
	private $scene;

	/**
	 * @param int                                                                $batch
	 * @param \SixtyEightPublishers\FixturesBundle\Scenario\Scene\SceneInterface $scene
	 */
	public function __construct(int $batch, SceneInterface $scene)
	{
		$this->batch = $batch;
		$this->scene = $scene;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return sprintf(
			'%s (%dx)',
			$this->scene->getName(),
			$this->batch
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFixtures(): array
	{
		return $this->scene->getFixtures();
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(DriverInterface $driver, LoggerInterface $logger, ?PurgeMode $purgeMode = NULL, array $parameters = []): void
	{
		for ($i = 1; $i <= $this->batch; $i++) {
			$logger->info(sprintf(
				'Running batch %d/%d:',
				$i,
				$this->batch
			));

			$this->scene->run($driver, $logger, $purgeMode, $parameters);
		}
	}
}
