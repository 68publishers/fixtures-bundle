<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\FileResolver;

use Tester\Assert;
use Tester\TestCase;
use Nelmio\Alice\FileLocator\DefaultFileLocator;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;
use SixtyEightPublishers\FixturesBundle\FileResolver\FileResolver;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleFileLocator;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;
use SixtyEightPublishers\FixturesBundle\FileResolver\RelativeFileResolver;

require __DIR__ . '/../../bootstrap.php';

final class RelativeFileResolverTest extends TestCase
{
	/** @var \SixtyEightPublishers\FixturesBundle\FileResolver\RelativeFileResolver|NULL */
	private $fileResolver;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		$bundleMap = new BundleMap([
			'foo_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle'),
			'bar_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle'),
		]);

		$fixtureDirs = [__DIR__ . '/../../resources/fixtures'];

		$this->fileResolver = new RelativeFileResolver(
			new FileResolver(
				new BundleFileLocator(new DefaultFileLocator(), $bundleMap),
				$fixtureDirs
			),
			$bundleMap,
			$fixtureDirs
		);
	}

	public function testResolverFiles(): void
	{
		$files = $this->fileResolver->resolve([
			'@foo_bundle/empty_fixture_1.yaml',
			'@bar_bundle/dummy/empty_fixture_1.yaml',
			'@bar_bundle/dummy',
			'empty_fixture_1.yaml',
			'empty_fixture_2.php',
			'foo',
		]);

		Assert::equal(
			[
				'@foo_bundle/empty_fixture_1.yaml',
				'@bar_bundle/dummy/empty_fixture_1.yaml',
				'@bar_bundle/dummy/empty_fixture_2.yaml',
				'empty_fixture_1.yaml',
				'empty_fixture_2.php',
				'foo/empty_fixture_3.json',
				'foo/empty_fixture_4.yaml',
			],
			$files
		);
	}

	public function testThrowExceptionWhenBundleFileIsMissing(): void
	{
		Assert::throws(function () {
			$this->fileResolver->resolve([
				'@foo_bundle/non-existent-file.yaml',
			]);
		}, FileNotFoundException::class);
	}

	public function testThrowExceptionWhenFixtureFileIsMissing(): void
	{
		Assert::throws(function () {
			$this->fileResolver->resolve([
				'path/to/non-existent-file.yaml',
			]);
		}, FileNotFoundException::class);
	}
}

(new RelativeFileResolverTest())->run();
