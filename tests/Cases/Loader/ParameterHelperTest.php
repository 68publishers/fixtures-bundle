<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Tests\Cases\Loader;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\FixturesBundle\Loader\ParametersHelper;

require __DIR__ . '/../../bootstrap.php';

final class ParameterHelperTest extends TestCase
{
	public function testFlattenWithoutPrefix(): void
	{
		$definitions[] = [
			[
				'first_1' => 1,
				'first_2' => 'foo',
				'first_3' => ['a', 'b', 'c'],
			],
			[
				'first_1' => 1,
				'first_2' => 'foo',
				'first_3' => ['a', 'b', 'c'],
			],
		];

		$definitions[] = [
			[
				'first_1' => [
					'second_1' => [
						'third_1' => [
							'foo' => 'dummy',
							'bar' => ['a', 'b', 'c'],
						],
					],
					'second_2' => 13,
					'second_3' => [1, 2, 3, 4, 5],
				],
				'first_2' => [
					'second_1' => [
						'a' => 'a',
						'b',
						'c',
					],
				],
			],
			[
				'first_1.second_1.third_1.foo' => 'dummy',
				'first_1.second_1.third_1.bar' => ['a', 'b', 'c'],
				'first_1.second_2' => 13,
				'first_1.second_3' => [1, 2, 3, 4, 5],
				'first_2.second_1.a' => 'a',
				'first_2.second_1.0' => 'b',
				'first_2.second_1.1' => 'c',
			],
		];

		foreach ($definitions as $_ => [$input, $output]) {
			Assert::equal($output, ParametersHelper::flatten($input));
		}
	}

	public function testFlattenWithPrefix(): void
	{
		$definitions[] = [
			[
				'first_1' => 1,
				'first_2' => 'foo',
				'first_3' => ['a', 'b', 'c'],
			],
			[
				'app.first_1' => 1,
				'app.first_2' => 'foo',
				'app.first_3' => ['a', 'b', 'c'],
			],
		];

		$definitions[] = [
			[
				'first_1' => [
					'second_1' => [
						'third_1' => [
							'foo' => 'dummy',
							'bar' => ['a', 'b', 'c'],
						],
					],
					'second_2' => 13,
					'second_3' => [1, 2, 3, 4, 5],
				],
				'first_2' => [
					'second_1' => [
						'a' => 'a',
						'b',
						'c',
					],
				],
			],
			[
				'app.first_1.second_1.third_1.foo' => 'dummy',
				'app.first_1.second_1.third_1.bar' => ['a', 'b', 'c'],
				'app.first_1.second_2' => 13,
				'app.first_1.second_3' => [1, 2, 3, 4, 5],
				'app.first_2.second_1.a' => 'a',
				'app.first_2.second_1.0' => 'b',
				'app.first_2.second_1.1' => 'c',
			],
		];

		foreach ($definitions as $_ => [$input, $output]) {
			Assert::equal($output, ParametersHelper::flatten($input, 'app'));
		}
	}
}

(new ParameterHelperTest())->run();
