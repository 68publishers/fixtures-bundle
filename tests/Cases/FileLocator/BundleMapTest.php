<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\FileLocator;

use Tester\Assert;
use Tester\TestCase;
use InvalidArgumentException;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

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

	public function testLocateFile(): void
	{
		Assert::same(
			realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle/empty_fixture_1.yaml'),
			$this->bundleMap->locate('@foo_bundle/empty_fixture_1.yaml')
		);
	}

	public function testLocateDirectory(): void
	{
		Assert::same(
			realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy'),
			$this->bundleMap->locate('@bar_bundle/dummy')
		);
	}

	public function testThrowExceptionWhenAtIsMissingInFilename(): void
	{
		Assert::throws(function () {
			$this->bundleMap->locate('foo_bundle/empty_fixture_1.yml');
		}, InvalidArgumentException::class, 'A path must start with @.');
	}

	public function testThrowExceptionWhenBundleIsNotDefined(): void
	{
		Assert::throws(function () {
			$this->bundleMap->locate('@baz_bundle/missing_fixture.yml');
		}, InvalidArgumentException::class, 'An bundle "baz_bundle" isn\'t defined in the map.');
	}

	public function testThrowExceptionWhenPathIsInvalid(): void
	{
		Assert::throws(function () {
			$this->bundleMap->locate('@foo_bundle/missing_fixture.yml');
		}, FileNotFoundException::class, 'Unable to find file or directory "@foo_bundle/missing_fixture.yml".');
	}
}

(new BundleMapTest())->run();
