<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context;

interface IContext
{
	/**
	 * @param string $type
	 *
	 * @return \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext
	 */
	public function resolveContext(string $type): IContext;

	/**
	 * @param string $type
	 * @param mixed  ...$args
	 *
	 * @return void
	 */
	public function bindContext(string $type, ...$args): void;

	/**
	 * @param string $type
	 *
	 * @return void
	 */
	public function unbindContext(string $type): void;

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set(string $key, $value): void;

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * @return string
	 */
	public function __toString(): string;
}
