<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager;

use Doctrine\Common\EventManager;

final class EventManagerRestrictorFactory implements EventManagerRestrictorFactoryInterface
{
	/** @var bool  */
	private $allowAll;

	/** @var array  */
	private $excluded;

	/**
	 * @param bool  $allowAll
	 * @param array $excluded
	 */
	public function __construct(bool $allowAll, array $excluded = [])
	{
		$this->allowAll = $allowAll;
		$this->excluded = $excluded;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(EventManager $eventManager): EventManagerRestrictorInterface
	{
		return new EventManagerRestrictor($eventManager, $this->allowAll, $this->excluded);
	}
}
