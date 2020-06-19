<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Helper;

use Nette\StaticClass;
use Tester\Environment;

final class FrameworkVersionChecker
{
	use StaticClass;

	/**
	 * @return float
	 */
	public static function getVersion(): float
	{
		return NETTE_VERSION;
	}

	/**
	 * @return void
	 */
	public static function check24(): void
	{
		if (2.4 !== NETTE_VERSION) {
			Environment::skip('Nette 2.4 only.');
		}
	}

	/**
	 * @return void
	 */
	public static function check3(): void
	{
		if (3.0 !== NETTE_VERSION) {
			Environment::skip('Nette 3 only.');
		}
	}
}
