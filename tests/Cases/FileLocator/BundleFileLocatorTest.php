<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\FileLocator;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Nelmio\Alice\FileLocatorInterface;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleMap;
use SixtyEightPublishers\FixturesBundle\FileLocator\BundleFileLocator;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

require __DIR__ . '/../../bootstrap.php';

final class BundleFileLocatorTest extends TestCase
{
	/** @var \SixtyEightPublishers\FixturesBundle\FileLocator\BundleFileLocator|NULL */
	private $bundleFileLocator;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		$fileLocator = Mockery::mock(FileLocatorInterface::class);

		$fileLocator->shouldReceive('locate')
			->andReturn('located-file');

		$bundleMap = new BundleMap([
			'foo_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle'),
			'bar_bundle' => realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle'),
		]);

		$this->bundleFileLocator = new BundleFileLocator($fileLocator, $bundleMap);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	public function testLocateBundleFile(): void
	{
		Assert::same(
			realpath(__DIR__ . '/../../resources/bundle_locator/foo_bundle/empty_fixture_1.yaml'),
			$this->bundleFileLocator->locate('@foo_bundle/empty_fixture_1.yaml')
		);
	}

	public function testLocateBundleDirectory(): void
	{
		Assert::same(
			realpath(__DIR__ . '/../../resources/bundle_locator/bar_bundle/dummy'),
			$this->bundleFileLocator->locate('@bar_bundle/dummy')
		);
	}

	public function testThrowExceptionWhenPathIsInvalid(): void
	{
		Assert::throws(function () {
			$this->bundleFileLocator->locate('@foo_bundle/missing_fixture.yml');
		}, FileNotFoundException::class, 'Unable to find file or directory "@foo_bundle/missing_fixture.yml".');
	}

	public function testLocateNonBundleFile(): void
	{
		Assert::same(
			'located-file',
			$this->bundleFileLocator->locate('dummy')
		);
	}
}

(new BundleFileLocatorTest())->run();
