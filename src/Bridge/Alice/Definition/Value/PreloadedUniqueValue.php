<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Definition\Value\UniqueValue;

final class PreloadedUniqueValue implements ValueInterface
{
	/** @var \Nelmio\Alice\Definition\Value\UniqueValue  */
	private $uniqueValue;

	/** @var string  */
	private $column;

	/**
	 * @param \Nelmio\Alice\Definition\Value\UniqueValue $uniqueValue
	 * @param string                                     $column
	 */
	public function __construct(UniqueValue $uniqueValue, string $column)
	{
		$this->uniqueValue = $uniqueValue;
		$this->column = $column;
	}

	/**
	 * @return string
	 */
	public function getColumn(): string
	{
		return $this->column;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue(): UniqueValue
	{
		return $this->uniqueValue;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		$value = $this->uniqueValue->getValue();

		return sprintf(
			'(unique, preload %s) %s',
			$this->column,
			$value instanceof ValueInterface ? $value : var_export($value, TRUE)
		);
	}
}
