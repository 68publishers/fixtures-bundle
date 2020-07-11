# Fixtures Bundle

A [Nette](https://nette.org) bundle to manage fixtures with an integration of packages [nelmio/alice](https://github.com/nelmio/alice) and [theofidry/AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures).
The bundle supports Nette in versions `2.4` and `3`!

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

- Doctrine ORM 2.5+
- Doctrine ODM 1.2+
- Doctrine PHPCR 1.4+

Eloquent and Propel 2 are not supported by this integration.

Continue to the integration guide [here](docs/integration/alice-data-fixutres.md).

## Integration of Fixtures Bundle

The Fixtures Bundle wraps functionality from both integrations and allows you to run fixtures in defined scenarios.

Continue to the integration guide [here](docs/integration/fixtures-bundle.md).

## Contributing

Before committing any changes, don't forget to run

```bash
$ vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
$ composer run tests
```
