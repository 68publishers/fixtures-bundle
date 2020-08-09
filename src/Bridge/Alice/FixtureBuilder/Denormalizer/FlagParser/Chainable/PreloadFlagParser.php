<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Definition\FlagBag;
use SixtyEightPublishers\FixturesBundle\Bridge\Alice\Definition\Flag\PreloadFlag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ChainableFlagParserInterface;

final class PreloadFlagParser implements ChainableFlagParserInterface
{
	use IsAServiceTrait;

	private const REGEX = '/^preload (?<column>.+)$/';

	/**
	 * @inheritdoc
	 */
	public function canParse(string $element, array &$matches = []): bool
	{
		return (bool) preg_match(self::REGEX, $element, $matches);
	}

	/**
	 * @inheritdoc
	 */
	public function parse(string $element): FlagBag
	{
		$matches = [];

		$this->canParse($element, $matches);

		return (new FlagBag(''))->withFlag(new PreloadFlag($matches['column']));
	}
}
