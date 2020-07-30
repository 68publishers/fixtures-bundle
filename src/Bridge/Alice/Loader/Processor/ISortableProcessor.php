<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\Processor;

interface ISortableProcessor
{
	/**
	 * Returns sorted input data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function __invoke(array $data): array;
}
