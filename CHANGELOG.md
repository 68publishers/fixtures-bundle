# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.0] - 2026-10-28
### Added
- Support for `doctrine/dbal` v4, `doctrine/orm` v3, `doctrine/data-fixtures` v2, `nette/php-generator` v4 and Symfony v8.
- Suggested packages `doctrine/orm` and `doctrine/data-fixtures`.
- GitHub Actions workflows (coding style, tests on PHP 8.3, 8.4 and 8.5 with lowest and highest dependencies).

### Changed
- Minimal PHP version is 8.3.
- **BC break:** Nette 2.4 is no longer supported, minimal `nette/di` version is 3.1.3 (older versions do not run on PHP 8.3).
- **BC break:** Conflicts with `doctrine/dbal` < 3.3 (MySQL is detected by `AbstractMySQLPlatform`), `doctrine/data-fixtures` < 1.5.1 (not compatible with DBAL 3) and `doctrine/persistence` < 1.3.
- Minimal `doctrine/event-manager` version is 1.2 (`EventManager::getAllListeners()` is used).
- Direct dependencies on `fakerphp/faker` ^1.23.1 (no PHP 8.3 deprecation in `Generator::seed()`), `nette/php-generator` ^3.5 || ^4.0 and `symfony/property-access` ^6.4 || ^7.0 || ^8.0, the bundle uses them directly.
- The console commands (`console.neon`) are registered only when `symfony/console` is installed.
- Nullable parameters are explicitly nullable, PHP 8.4 no longer emits the implicit nullable deprecation.
- `PreloadedUniqueValueDenormalizer::denormalize()` has the signature `(FixtureInterface $scope, ?FlagBag $flags, $value)` (no optional parameter before a required one).
- Fixture paths are exported into the DI container with `Nette\PhpGenerator\Literal` and `var_export()` instead of the deprecated `PhpLiteral` and `addslashes()`.
- Php-Cs-Fixer v3 (`.php_cs.dist` migrated to `.php-cs-fixer.dist.php`), `mockery/mockery` ^1.6.12, `nette/tester` ^2.6.

### Removed
- **BC break:** Support for PHP < 8.3 and Nette 2.4, including the abstract method `Bridge\Nette\CompilerExtension::getNette24Config()` and its implementations, and the file `Bridge/Nette/nette.di.compatibility.php`.
- Travis CI configuration and the unused dev dependency `nette/application`.

### Fixed
- `Purger` used `Connection::exec()`, which was removed in DBAL 4. `Connection::executeStatement()` is used instead.
- `Purger` did not disable foreign key checks on MySQL when the DBAL driver was wrapped by a middleware (e.g. logging). MySQL is now detected by the database platform.
- `Purger` failed with a `NULL` purge mode.
- `fixtures:list -f` (the option `--format` without a value) failed with a `TypeError`. The table format is used.

## [0.3.1] - 2024-08-06
### Changed
- Allowed `nette/utils` v4.

## [0.3.0] - 2023-08-21
### Changed
- Compatibility with `doctrine/persistence` namespace `Doctrine\Persistence`.

### Fixed
- `SequenceIdGenerator` passes a string into `str_replace()`.

## [0.2.0] - 2023-01-03
### Added
- PHP 8.1 support.

### Changed
- Added the support warning into the README.

## [0.1.10] - 2021-02-03
### Fixed
- The normalization callback for the option `object_loaders` in `FixturesBundleExtension`.

## [0.1.9] - 2020-11-26
### Fixed
- Configuration and strict types fixes.

## [0.1.8] - 2020-08-15
### Added
- Option `excluded` in the AliceDataFixtures bridge (excluded tables for the ORM purger).

## [0.1.7] - 2020-08-14
### Fixed
- The loop condition in `BatchedScene::run()`.

## [0.1.6] - 2020-08-14
### Added
- Scenes (parts of scenarios).
- Object loaders.

## [0.1.5] - 2020-08-09
### Added
- Flag `preload`.

## [0.1.4] - 2020-08-03
### Added
- Support for nested parameters (a dot notation) inside the provider `<parameters()>`.

## [0.1.3] - 2020-08-03
### Added
- Workaround for dynamic parameters.
- Management of event listeners/subscribers.

## [0.1.2] - 2020-07-30
### Added
- `SortableDataLoader`.

### Changed
- Updated docs.

## [0.1.1] - 2020-07-12
### Changed
- Application parameters have flattened keys.
- Updated docs.

## [0.1] - 2020-07-11
First release.

- Nette `^2.4 || ^3.0`
- Alice `^3.7`
- AliceDataFixtures `^1.1`

[Unreleased]: https://github.com/68publishers/fixtures-bundle/compare/v0.4.0...HEAD
[0.4.0]: https://github.com/68publishers/fixtures-bundle/compare/v0.3.1...v0.4.0
[0.3.1]: https://github.com/68publishers/fixtures-bundle/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/68publishers/fixtures-bundle/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.10...v0.2.0
[0.1.10]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.9...v0.1.10
[0.1.9]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.8...v0.1.9
[0.1.8]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.7...v0.1.8
[0.1.7]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.6...v0.1.7
[0.1.6]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.5...v0.1.6
[0.1.5]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.4...v0.1.5
[0.1.4]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/68publishers/fixtures-bundle/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/68publishers/fixtures-bundle/compare/v0.1...v0.1.1
[0.1]: https://github.com/68publishers/fixtures-bundle/releases/tag/v0.1
