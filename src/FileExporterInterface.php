<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle;

interface FileExporterInterface
{
	/**
	 * Returns paths to ALL available fixtures
	 *
	 * @return string[]
	 */
	public function export(): array;
}
