<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use InvalidArgumentException;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI\FidryAliceDataFixturesExtension;

final class Scenario implements IScenario
{
	/** @var string  */
	private $name;

	/** @var string[]  */
	private $fixtures;

	/** @var \Fidry\AliceDataFixtures\Persistence\PurgeMode|string|NULL  */
	private $purgeMode;

	/**
	 * @param string      $name
	 * @param string[]    $fixtures
	 * @param string|NULL $purgeMode
	 */
	public function __construct(string $name, array $fixtures, ?string $purgeMode)
	{
		$this->name = $name;
		$this->fixtures = $fixtures;
		$this->purgeMode = $purgeMode;
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
	public function getFixtures(): array
	{
		return $this->fixtures;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurgeMode(): ?PurgeMode
	{
		if (NULL === $this->purgeMode) {
			return NULL;
		}

		if (!$this->purgeMode instanceof PurgeMode) {
			$this->purgeMode = $this->createPurgeMode();
		}

		return $this->purgeMode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPurgeMode(?string $purgeMode): void
	{
		$this->purgeMode = $purgeMode;
	}

	/**
	 * @return \Fidry\AliceDataFixtures\Persistence\PurgeMode
	 * @throws \InvalidArgumentException
	 */
	private function createPurgeMode(): PurgeMode
	{
		switch ($this->purgeMode) {
			case FidryAliceDataFixturesExtension::PURGE_MODE_DELETE:
				return PurgeMode::createDeleteMode();
			case FidryAliceDataFixturesExtension::PURGE_MODE_TRUNCATE:
				return PurgeMode::createTruncateMode();
			case FidryAliceDataFixturesExtension::PURGE_MODE_NO_PURGE:
				return PurgeMode::createNoPurgeMode();
		}

		throw new InvalidArgumentException(sprintf(
			'An invalid purge mode "%s" passed into a scenario "%s".',
			$this->purgeMode,
			$this->name
		));
	}
}
