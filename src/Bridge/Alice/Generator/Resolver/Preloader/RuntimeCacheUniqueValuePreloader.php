<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader;

use Nelmio\Alice\IsAServiceTrait;

final class RuntimeCacheUniqueValuePreloader implements IUniqueValuePreloader
{
	use IsAServiceTrait;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\IUniqueValuePreloader  */
	private $preloader;

	/** @var array  */
	private $cache = [];

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\IUniqueValuePreloader $preloader
	 */
	public function __construct(IUniqueValuePreloader $preloader)
	{
		$this->preloader = $preloader;
	}

	/**
	 * {@inheritDoc}
	 */
	public function preload(string $className, string $column): array
	{
		$key = $className . '::$' . $column;

		if (!array_key_exists($key, $this->cache)) {
			$this->cache[$key] = $this->preloader->preload($className, $column);
		}

		return $this->cache[$key];
	}
}
