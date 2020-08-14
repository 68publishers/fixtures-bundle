<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\Id;

final class SequenceIdGenerator implements IdGeneratorInterface
{
	/** @var string  */
	private $mask;

	/** @var int  */
	private $currentId;

	/**
	 * @param string $mask
	 * @param int    $start
	 */
	public function __construct(string $mask = '', int $start = 1)
	{
		if (FALSE === strpos($mask, '*')) {
			$mask .= '*';
		}

		$this->mask = $mask;
		$this->currentId = $start;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate(): string
	{
		$id = str_replace('*', $this->currentId, $this->mask);

		$this->currentId++;

		return $id;
	}
}
