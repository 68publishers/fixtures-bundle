<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;

final class NamedPurgeMode
{
	/** @var string  */
	private $name;

	/** @var \Fidry\AliceDataFixtures\Persistence\PurgeMode  */
	private $purgeMode;

	/**
	 * @param string                                         $name
	 * @param \Fidry\AliceDataFixtures\Persistence\PurgeMode $purgeMode
	 */
	public function __construct(string $name, PurgeMode $purgeMode)
	{
		$this->name = $name;
		$this->purgeMode = $purgeMode;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return \Fidry\AliceDataFixtures\Persistence\PurgeMode
	 */
	public function getMode(): PurgeMode
	{
		return $this->purgeMode;
	}
}
