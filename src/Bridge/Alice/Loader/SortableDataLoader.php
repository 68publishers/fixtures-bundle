<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader;

use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\DataLoaderInterface;

final class SortableDataLoader implements DataLoaderInterface
{
	use IsAServiceTrait;

	/** @var \Nelmio\Alice\DataLoaderInterface  */
	private $dataLoader;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\Processor\SortableProcessorInterface  */
	private $sortableProcessor;

	/**
	 * @param \Nelmio\Alice\DataLoaderInterface                                                             $dataLoader
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\Processor\SortableProcessorInterface $sortableProcessor
	 */
	public function __construct(DataLoaderInterface $dataLoader, Processor\SortableProcessorInterface $sortableProcessor)
	{
		$this->dataLoader = $dataLoader;
		$this->sortableProcessor = $sortableProcessor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
	{
		$sortableProcessor = $this->sortableProcessor;

		return $this->dataLoader->loadData($sortableProcessor($data), $parameters, $objects);
	}
}
