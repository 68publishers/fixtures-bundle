<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;

interface ScenarioInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string[]
	 */
	public function getFixtures(): array;

	/**
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|NULL
	 */
	public function getPurgeMode(): ?NamedPurgeMode;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|NULL $purgeMode
	 *
	 * @return void
	 */
	public function setPurgeMode(?NamedPurgeMode $purgeMode): void;

	/**
	 * @return \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectLoaderInterface[]
	 */
	public function getObjectLoaders(): array;
}
