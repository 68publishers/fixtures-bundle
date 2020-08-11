<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Loader\ObjectLoader\Id;

use RuntimeException;

final class ValueIdGenerator implements IdGeneratorInterface
{
	/** @var string  */
	private $id;

	/** @var bool  */
	private $used = FALSE;

	/**
	 * @param string $id
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate(): string
	{
		if (TRUE === $this->used) {
			throw new RuntimeException(sprintf(
				'The value "%s" of an ID generator %s can be used only once.',
				$this->id,
				__CLASS__
			));
		}

		$this->used = TRUE;

		return $this->id;
	}
}
