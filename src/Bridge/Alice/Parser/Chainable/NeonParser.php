<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Parser\Chainable;

use Nette\Neon\Neon;
use Nette\Neon\Exception as NeonParseException;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Throwable\Exception\Parser\ParseExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class NeonParser implements ChainableParserInterface
{
	/**
	 * @inheritdoc
	 */
	public function canParse(string $file): bool
	{
		if (FALSE === stream_is_local($file)) {
			return FALSE;
		}

		return 1 === preg_match('/.+\.neon$/i', $file);
	}

	/**
	 * @inheritdoc
	 */
	public function parse(string $file): array
	{
		if (FALSE === file_exists($file)) {
			throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file);
		}

		try {
			return (array) Neon::decode(file_get_contents($file));
		} catch (\Exception $exception) {
			if ($exception instanceof NeonParseException) {
				throw new UnparsableFileException(
					sprintf('The file "%s" does not contain valid NEON.', $file),
					0,
					$exception
				);
			}

			throw ParseExceptionFactory::createForUnparsableFile($file, 0, $exception);
		}
	}
}
