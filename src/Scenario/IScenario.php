<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;

interface IScenario
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
	 * @return \Fidry\AliceDataFixtures\Persistence\PurgeMode|NULL
	 */
	public function getPurgeMode(): ?PurgeMode;

	/**
	 * @param string|NULL $purgeMode
	 *
	 * @return void
	 */
	public function setPurgeMode(?string $purgeMode): void;
}
