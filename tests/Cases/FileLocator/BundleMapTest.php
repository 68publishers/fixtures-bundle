<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\FileLocator;

use Tester\Assert;
use Tester\TestCase;
use InvalidArgumentException;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;

require __DIR__ . '/../../bootstrap.php';

final class BundleMapTest extends TestCase
{
	/** @var \SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap|NULL */
	private $bundleMap;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		$this->bundleMap = new BundleMap([
			'foo_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle'),
			'bar_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle'),
		]);
	}

	public function testIsBundlePath(): void
	{
		Assert::true($this->bundleMap->isBundlePath('@foo_bundle/empty_fixture_1.yaml'));
		Assert::false($this->bundleMap->isBundlePath('empty_fixture_1.yaml'));
	}

	public function testParseBundlePath(): void
	{
		Assert::same(
			$this->bundleMap->parseBundlePath('@foo_bundle/empty_fixture_1.yaml'),
			['foo_bundle', 'empty_fixture_1.yaml']
		);

		Assert::same(
			$this->bundleMap->parseBundlePath('bar_bundle/dummy/empty_fixture_1.yaml'),
			['bar_bundle', 'dummy/empty_fixture_1.yaml']
		);
	}

	public function testGetFixturesDirectory(): void
	{
		Assert::same(
			$this->bundleMap->getFixturesDirectory('foo_bundle'),
			realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle')
		);

		Assert::same(
			$this->bundleMap->getFixturesDirectory('bar_bundle'),
			realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle')
		);

		Assert::throws(function () {
			$this->bundleMap->getFixturesDirectory('baz_bundle');
		}, InvalidArgumentException::class, 'An bundle "baz_bundle" isn\'t defined in the map.');
	}

	public function testToArray(): void
	{
		Assert::equal(
			$this->bundleMap->toArray(),
			[
				'foo_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle'),
				'bar_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle'),
			]
		);
	}
}

(new BundleMapTest())->run();
