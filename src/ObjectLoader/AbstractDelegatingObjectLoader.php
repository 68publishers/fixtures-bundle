<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\ObjectLoader;

use InvalidArgumentException;

abstract class AbstractDelegatingObjectLoader implements ObjectLoaderInterface
{
	private const NAMESPACES = [
		'Doctrine\\Persistence\\ObjectManager' => __NAMESPACE__ . '\\ObjectManager',
	];

	/** @var \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface  */
	protected $idGenerator;

	/** @var string  */
	protected $className;

	/** @var array  */
	protected $args;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\IdGeneratorInterface $idGenerator
	 * @param string                                                                    $className
	 * @param mixed                                                                     ...$args
	 */
	public function __construct(Id\IdGeneratorInterface $idGenerator, string $className, ...$args)
	{
		$this->idGenerator = $idGenerator;
		$this->className = $className;
		$this->args = $args;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load($storageDriver): array
	{
		foreach (self::NAMESPACES as $class => $delegatedNamespace) {
			if (!$storageDriver instanceof $class) {
				continue;
			}

			$shortName = explode('\\', static::class);
			$className = $delegatedNamespace . '\\' . array_pop($shortName);
			$args = array_merge([$this->idGenerator, $this->getClassName()], $this->args);

			return (new $className(...$args))->load($storageDriver);
		}

		throw new InvalidArgumentException(sprintf(
			'A delegated ObjectLoader for the class %s and the storage driver %s not found.',
			static::class,
			get_class($storageDriver)
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClassName(): string
	{
		return $this->className;
	}
}
