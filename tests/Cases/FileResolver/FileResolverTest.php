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

require __DIR__ . '/../../bootstrap.php';

final class FileResolverTest extends TestCase
{
	/** @var \SixtyEightPublishers\FixturesBundle\FileResolver\FileResolver|NULL */
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

		$this->fileResolver = new FileResolver(
			new BundleFileLocator(new DefaultFileLocator(), $bundleMap),
			$bundleMap,
			[__DIR__ . '/../../resources/fixtures']
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
				realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle/empty_fixture_1.yaml'),
				realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy/empty_fixture_1.yaml'),
				realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy/empty_fixture_2.yaml'),
				realpath(__DIR__ . '/../../resources/fixtures/empty_fixture_1.yaml'),
				realpath(__DIR__ . '/../../resources/fixtures/empty_fixture_2.php'),
				realpath(__DIR__ . '/../../resources/fixtures/foo/empty_fixture_3.json'),
				realpath(__DIR__ . '/../../resources/fixtures/foo/empty_fixture_4.yaml'),
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

	public function testExport(): void
	{
		Assert::equal(
			$this->fileResolver->export(),
			[
				realpath(__DIR__ . '/../../resources/fixtures/foo/empty_fixture_3.json'),
				realpath(__DIR__ . '/../../resources/fixtures/foo/empty_fixture_4.yaml'),
				realpath(__DIR__ . '/../../resources/fixtures/empty_fixture_2.php'),
				realpath(__DIR__ . '/../../resources/fixtures/empty_fixture_1.yaml'),
				realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle/empty_fixture_1.yaml'),
				realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy/empty_fixture_2.yaml'),
				realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy/empty_fixture_1.yaml'),
			]
		);
	}
}

(new FileResolverTest())->run();
