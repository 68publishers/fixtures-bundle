<?php

declare(strict_types=1);

namespace SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class NelmioAliceExtension extends AbstractNelmioAliceExtension
{
	/**
	 * @return \Nette\Schema\Schema
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'locale' => Expect::string('en_US'),
			'seed' => Expect::int(1),
			'loading_limit' => Expect::int(5),
			'functions_blacklist' => Expect::array(['current']),
			'max_unique_values_retry' => Expect::int(150),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getValidConfig(): object
	{
		return $this->config;
	}
}
