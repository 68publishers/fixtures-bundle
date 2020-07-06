<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;

final class Scenario implements IScenario
{
	/** @var string  */
	private $name;

	/** @var string[]  */
	private $fixtures;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|string|NULL  */
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
}
