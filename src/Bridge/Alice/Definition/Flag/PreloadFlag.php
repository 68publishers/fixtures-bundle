<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;

final class PreloadFlag implements FlagInterface
{
	/** @var string  */
	private $column;

	/**
	 * @param string $column
	 */
	public function __construct(string $column)
	{
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
	public function __toString(): string
	{
		return 'preload';
	}
}
