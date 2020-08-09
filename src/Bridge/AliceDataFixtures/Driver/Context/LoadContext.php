<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context;

class LoadContext extends AbstractContext
{
	public const KEY_DRIVER = 'driver';

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext $mainContext
	 * @param string                                                                                $driver
	 */
	public function __construct(IContext $mainContext, string $driver)
	{
		parent::__construct($mainContext, [
			self::KEY_DRIVER => $driver,
		]);
	}

	/**
	 * @return string
	 */
	public function getDriver(): string
	{
		return $this->get(self::KEY_DRIVER);
	}
}
