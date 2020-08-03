<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Faker\Provider;

use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundExceptionFactory;

/**
 * Workaround for an issue https://github.com/nelmio/alice/issues/902
 */
final class ParametersProvider
{
	/** @var \Nelmio\Alice\ParameterBag  */
	private $parameterBag;

	public function __construct()
	{
		$this->setParameterBag(new ParameterBag());
	}

	/**
	 * @param string $name
	 * @param bool   $nested
	 *
	 * @return mixed
	 */
	public function parameter(string $name, bool $nested = TRUE)
	{
		if (TRUE === $nested && FALSE !== strpos($name, '.')) {
			$parts = explode('.', $name);
			$name = array_shift($parts);
		}

		$parameter = $this->parameterBag->get($name);

		if (isset($parts) && !empty($parts)) {
			$parameter = $this->filterParameterParts($parameter, $name, $parts);
		}

		return $parameter;
	}

	/**
	 * @internal
	 * @param \Nelmio\Alice\ParameterBag $parameterBag
	 *
	 * @return void
	 */
	public function setParameterBag(ParameterBag $parameterBag): void
	{
		$this->parameterBag = $parameterBag;
	}

	/**
	 * @param mixed  $parameter
	 * @param string $rootName
	 * @param array  $parts
	 *
	 * @return mixed
	 */
	private function filterParameterParts($parameter, string $rootName, array $parts)
	{
		foreach ($parts as $part) {
			if (!is_array($parameter)) {
				throw new ParameterNotFoundException(sprintf(
					'Can not resolve a parameter %s.%s because the parameter %s is not an array.',
					$rootName,
					$part,
					$rootName
				));
			}

			if (!array_key_exists($part, $parameter)) {
				throw ParameterNotFoundExceptionFactory::create($rootName . '.' . $part);
			}

			$parameter = $parameter[$part];
			$rootName .= '.' . $part;
		}

		return $parameter;
	}
}
