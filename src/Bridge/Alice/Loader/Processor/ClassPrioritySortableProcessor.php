<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\Processor;

final class ClassPrioritySortableProcessor implements SortableProcessorInterface
{
	/** @var int[] */
	private $priorityMap;

	/**
	 * @param array $priorityMap
	 */
	public function __construct(array $priorityMap)
	{
		$this->priorityMap = $priorityMap;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(array $data): array
	{
		$sorted = [];

		foreach ($data as $className => $fixtures) {
			$sorted[array_key_exists($className, $this->priorityMap) ? $this->priorityMap[$className] : 0][$className] = $fixtures;
		}

		ksort($sorted);

		return array_merge([], ...array_reverse(array_values($sorted)));
	}
}
