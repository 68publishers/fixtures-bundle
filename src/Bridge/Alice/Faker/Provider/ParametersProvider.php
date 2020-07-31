<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\Faker\Provider;

use Nelmio\Alice\ParameterBag;

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
	 *
	 * @return mixed
	 */
	public function parameter(string $name)
	{
		return $this->parameterBag->get($name);
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
}
