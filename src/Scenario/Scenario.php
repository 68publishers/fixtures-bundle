<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Scenario;

use SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectLoaderInterface;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\PurgeModeFactory;

final class Scenario implements ScenarioInterface
{
	/** @var string  */
	private $name;

	/** @var string[]  */
	private $fixtures;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Persistence\NamedPurgeMode|string|NULL  */
	private $purgeMode;

	/** @var \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectLoaderInterface[] */
	private $objectLoaders;

	/**
	 * @param string      $name
	 * @param string[]    $fixtures
	 * @param string|NULL $purgeMode
	 * @param \SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\ObjectLoaderInterface[]
	 */
	public function __construct(string $name, array $fixtures, ?string $purgeMode, array $objectLoaders = [])
	{
		$this->name = $name;
		$this->fixtures = $fixtures;
		$this->purgeMode = $purgeMode;
		$this->objectLoaders = (static function (ObjectLoaderInterface ...$objectLoaders) {
			return $objectLoaders;
		})(...$objectLoaders);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFixtures(): array
	{
		return $this->fixtures;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPurgeMode(): ?NamedPurgeMode
	{
		if (NULL === $this->purgeMode) {
			return NULL;
		}

		if (!$this->purgeMode instanceof NamedPurgeMode) {
			$this->purgeMode = PurgeModeFactory::create($this->purgeMode);
		}

		return $this->purgeMode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPurgeMode(?NamedPurgeMode $purgeMode): void
	{
		$this->purgeMode = $purgeMode;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getObjectLoaders(): array
	{
		return $this->objectLoaders;
	}
}
