<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context;

use InvalidArgumentException;

abstract class AbstractContext implements IContext
{
	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext  */
	private $mainContext;

	/** @var array  */
	private $contextValues = [];

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext|null $mainContext
	 * @param array                                                                                      $contextValues
	 */
	public function __construct(?IContext $mainContext = NULL, array $contextValues = [])
	{
		$this->mainContext = $mainContext ?? $this;

		foreach ($contextValues as $key => $contextValue) {
			$this->set((string) $key, $contextValue);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolveContext(string $type): IContext
	{
		if ($this->mainContext !== $this) {
			return $this->mainContext->resolveContext($type);
		}

		return $this->get('__context.' . $type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function bindContext(string $type, ...$args): void
	{
		if ($this->mainContext !== $this) {
			$this->mainContext->bindContext($type, ...$args);

			return;
		}

		$this->set('__context.' . $type, new $type($this->mainContext, ...$args));
	}

	/**
	 * {@inheritDoc}
	 */
	public function unbindContext(string $type): void
	{
		if ($this->mainContext !== $this) {
			$this->mainContext->unbindContext($type);

			return;
		}

		$key = '__context.' . $type;

		if ($this->has($key)) {
			unset($this->contextValues[$key]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, $value): void
	{
		$this->contextValues[$key] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->contextValues);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key)
	{
		if (!$this->has($key)) {
			throw new InvalidArgumentException(sprintf(
				'Missing value for key "%s" in the context.',
				$key
			));
		}

		return $this->contextValues[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		if ($this->mainContext !== $this) {
			return (string) $this->mainContext;
		}

		return var_export($this, TRUE) ?? '(unresolved context)';
	}
}
