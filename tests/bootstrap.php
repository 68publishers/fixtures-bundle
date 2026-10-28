<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

define('TEMP_PATH', __DIR__ . '/temp');

\Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
