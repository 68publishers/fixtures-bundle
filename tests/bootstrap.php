<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

$netteVersion = 3.0;
$composerLock = __DIR__ . '/../composer.lock';

if (file_exists($composerLock)) {
	$composerLock = json_decode(file_get_contents($composerLock), TRUE);

	if (TRUE === ($composerLock['prefer-lowest'] ?? FALSE)) {
		$netteVersion = 2.4;
	}
}

define('NETTE_VERSION', $netteVersion);
define('TEMP_PATH', __DIR__ . '/temp');

\Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
