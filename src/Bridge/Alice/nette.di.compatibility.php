<?php

declare(strict_types=1);

namespace Nette\DI {
	if (!class_exists('Nette\\DI\\Definitions\\ServiceDefinition')) {
		class_alias('Nette\\DI\\ServiceDefinition', 'Nette\\DI\\Definitions\\ServiceDefinition');
	}

	if (!class_exists('Nette\\DI\\Definitions\\Statement')) {
		class_alias('Nette\\DI\\Statement', 'Nette\\DI\\Definitions\\Statement');
	}
}
