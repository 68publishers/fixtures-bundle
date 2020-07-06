<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence;

use InvalidArgumentException;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;

final class PurgeModeFactory
{
	public const PURGE_MODE_DELETE = 'delete';
	public const PURGE_MODE_TRUNCATE = 'truncate';
	public const PURGE_MODE_NO_PURGE = 'no_purge';

	public const PURGE_MODES = [
		self::PURGE_MODE_DELETE,
		self::PURGE_MODE_TRUNCATE,
		self::PURGE_MODE_NO_PURGE,
	];

	/**
	 * @param string $purgeMode
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode
	 * @throws \InvalidArgumentException
	 */
	public static function create(string $purgeMode): NamedPurgeMode
	{
		switch ($purgeMode) {
			case self::PURGE_MODE_DELETE:
				$mode = PurgeMode::createDeleteMode();

				break;
			case self::PURGE_MODE_TRUNCATE:
				$mode = PurgeMode::createTruncateMode();

				break;
			case self::PURGE_MODE_NO_PURGE:
				$mode = PurgeMode::createNoPurgeMode();

				break;
		}

		if (isset($mode)) {
			return new NamedPurgeMode($purgeMode, $mode);
		}

		throw new InvalidArgumentException(sprintf(
			'An invalid purge mode "%s".',
			$purgeMode
		));
	}
}
