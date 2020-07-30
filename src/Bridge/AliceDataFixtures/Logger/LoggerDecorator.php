<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Logger;

use Psr\Log\NullLogger;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class LoggerDecorator extends AbstractLogger
{
	/** @var \Psr\Log\LoggerInterface  */
	private $logger;

	/**
	 * @param \Psr\Log\LoggerInterface|NULL $logger
	 */
	public function __construct(?LoggerInterface $logger = NULL)
	{
		$this->setLogger($logger ?? new NullLogger());
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @return void
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @return bool
	 */
	public function hasLogger(): bool
	{
		return !($this->logger instanceof NullLogger);
	}

	/**
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return void
	 */
	public function log($level, $message, array $context = []): void
	{
		$this->logger->log($level, $message, $context);
	}
}
