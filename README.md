> :warning: Warning! This package does not have active support, it exists only for the historical needs of the author.

# Fixtures Bundle

A [Nette](https://nette.org) bundle to manage fixtures with an integration of packages [nelmio/alice](https://github.com/nelmio/alice) and [theofidry/AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures).

## Requirements

- PHP 8.3 or higher
- Nette DI 3.1+
- `symfony/console` for the console commands (optional, the commands are registered only when it is installed)
- `doctrine/orm` (2.10+ or 3), `doctrine/dbal` 3.3+ or 4 and `doctrine/data-fixtures` 1.5.1+ or 2 for the Doctrine ORM driver

## Installation

The best way to install 68publishers/fixtures-bundle is using Composer:

```bash
$ composer require 68publishers/fixtures-bundle
```

## Integration of Alice

Alice is a fixture generator that can hydrate entities or any objects from YAML, JSON, or raw PHP definitions. 
This package adds also a parser for [NEON](https://ne-on.org) files.

Continue to the integration guide [here](docs/integration/alice.md).

## Integration of AliceDataFixtures

A package AliceDataFixtures provides a persistent layer for data fixtures. The only supported ORM/ODM is Doctrine:

- Doctrine ORM 2.10+ and 3 (with DBAL 3.3+ or 4)
- Doctrine ODM 1.2+
- Doctrine PHPCR 1.4+

Eloquent and Propel 2 are not supported by this integration.

Continue to the integration guide [here](docs/integration/alice-data-fixtures.md).

## Integration of Fixtures Bundle

The Fixtures Bundle wraps functionality from both integrations and allows you to run fixtures in defined scenarios.

Continue to the integration guide [here](docs/integration/fixtures-bundle.md).

## Upgrade from 0.3

- Minimal PHP version is 8.3.
- Nette 2.4 is no longer supported, the bundle requires `nette/di` 3.1.3 or higher.
- `doctrine/dbal` < 3.3 and `doctrine/data-fixtures` < 1.5.1 are no longer supported (composer conflicts).
- The abstract method `SixtyEightPublishers\FixturesBundle\Bridge\Nette\CompilerExtension::getNette24Config()` has been removed, remove its implementation from your extensions if you extend the class.

## Contributing

Before committing any changes, don't forget to run

```bash
$ vendor/bin/php-cs-fixer fix -v --dry-run
```

and

```bash
$ composer run tests
```
