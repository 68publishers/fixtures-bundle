<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\EventManager;

use Doctrine\Common\EventManager;

final class EventManagerRestrictor implements EventManagerRestrictorInterface
{
	/** @var \Doctrine\Common\EventManager  */
	private $eventManager;

	/** @var bool  */
	private $allowAll;

	/** @var array  */
	private $excluded;

	/**
	 * @param \Doctrine\Common\EventManager $eventManager
	 * @param bool                          $allowAll
	 * @param array                         $excluded
	 */
	public function __construct(EventManager $eventManager, bool $allowAll = TRUE, array $excluded = [])
	{
		$this->eventManager = $eventManager;
		$this->allowAll = $allowAll;
		$this->excluded = $excluded;
	}

	/**
	 * {@inheritDoc}
	 */
	public function restrict(): void
	{
		$listeners = $this->eventManager->getAllListeners();

		foreach ($listeners as $eventName => $eventListeners) {
			foreach ($eventListeners as $eventListener) {
				if (!$this->isListenerAllowed($eventListener)) {
					$this->eventManager->removeEventListener($eventName, $eventListener);
				}
			}
		}
	}

	/**
	 * @param mixed $listener
	 *
	 * @return bool
	 */
	public function isListenerAllowed($listener): bool
	{
		$isExcluded = $this->isListenerExcluded($listener);

		return ($this->allowAll && !$isExcluded) || (!$this->allowAll && $isExcluded);
	}

	/**
	 * @param mixed $listener
	 *
	 * @return bool
	 */
	private function isListenerExcluded($listener): bool
	{
		if (is_array($listener)) {
			$listener = reset($listener);
		}

		foreach ($this->excluded as $className) {
			if ($listener instanceof $className) {
				return TRUE;
			}
		}

		return FALSE;
	}
}
