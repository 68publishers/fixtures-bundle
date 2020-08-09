<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Generator\Resolver\Preloader;

use RuntimeException;
use Nelmio\Alice\IsAServiceTrait;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext;
use SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Generator\Resolver\Preloader\IUniqueValuePreloader;

final class ContextBasedUniqueValuePreloader implements IUniqueValuePreloader
{
	use IsAServiceTrait;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext  */
	private $context;

	/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Generator\Resolver\Preloader\IChainableUniqueValuePreloader[] */
	private $preloaders;

	/**
	 * @param \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\IContext $context
	 * @param array                                                                                 $preloaders
	 */
	public function __construct(IContext $context, array $preloaders)
	{
		$this->context = $context;

		$this->preloaders = (static function (IChainableUniqueValuePreloader ...$preloaders) {
			return $preloaders;
		})(...$preloaders);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \RuntimeException
	 */
	public function preload(string $className, string $column): array
	{
		/** @var \SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\Context\LoadContext $context */
		$context = $this->context->resolveContext(LoadContext::class);

		$context->set('className', $className);
		$context->set('column', $column);

		foreach ($this->preloaders as $preloader) {
			if ($preloader->canPreload($context)) {
				return $preloader->preload($className, $column);
			}
		}

		throw new RuntimeException(sprintf(
			'Can\'t resolve a preloader for a column %s::$%s in the context %s',
			$className,
			$column,
			$context
		));
	}
}
