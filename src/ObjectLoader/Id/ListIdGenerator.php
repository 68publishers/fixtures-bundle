<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader\Id;

use RuntimeException;

final class ListIdGenerator implements IdGeneratorInterface
{
	/** @var string  */
	private $mask;

	/** @var array  */
	private $list;

	/** @var  */
	private $stack;

	/**
	 * @param string $mask
	 * @param array  $list
	 */
	public function __construct(string $mask, array $list)
	{
		if (FALSE === strpos($mask, '*')) {
			$mask .= '*';
		}

		$this->mask = $mask;
		$this->list = $this->stack = $list;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate(): string
	{
		if (0 >= count($this->stack)) {
			throw new RuntimeException(sprintf(
				'Can not generate an another ID because all values from a provided list [%s] have been used.',
				implode(', ', $this->list)
			));
		}

		$value = array_shift($this->stack);

		return str_replace('*', $value, $this->mask);
	}
}
