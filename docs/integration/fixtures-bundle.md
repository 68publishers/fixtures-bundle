# Integration of Fixtures Bundle

## Integration

Fixtures are located in directories `%appDir%/fixtures` or `%appDir%/../fixture` by default but you can change these paths of course. Also, other paths can be defined by other Compiler Extensions.
__
```neon
extensions:
	68publishers.fixtures_bundle: SixtyEightPublishers\FixturesBundle\DI\FixturesBundleExtension

# default configuration
68publishers.fixtures_bundle:
	fixture_dirs: [%appDir%/fixtures, %appDir%/../fixtures]
	scenarios: []
```

Each scenario can define its own purge mode that will override a default purge mode (defined in `AliceDataFixtures` bridge). Here is an example of scenarios configuration:

```neon
68publishers.fixtures_bundle:
	scenarios:
		default:
			fixtures:
				- admin-users.neon # points to the file "%appDir%/fixtures/admin-users.neon" or "%appDir%/../fixtures/admin-users.neon"
				- dummy-users.neon # points to the file "%appDir%/fixtures/dummy-users.neon" or "%appDir%/../fixtures/dummy-users.neon"
				- articles # points to the directory "%appDir%/fixtures/articles" or "%appDir%/../fixtures/articles"
				# all fixtures in the directory will be recursively included
				- @product_bundle/products/apple_products.yaml # points to the file "products/apple_products.yaml" located in some Product Bundle
		dummy-users:
			purge_mode: no_purge # database will not be purged
			fixtures:
				- dummy-users.neon
```

## Object loaders

In most cases, you need some existing entities from the database for associations when your scenario is in a purge mode `no_purge`.
For this cases are here an object loaders:

```neon
68publishers.fixtures_bundle:
	scenarios:
		dummy-events:
			purge_mode: no_purge
			fixtures:
				- dummy-events.neon
			object_loaders:
				# preload 50 places from database, objects are available in the fixtures under ids `place_1`, `place_2`, ..., `place_50`:
				- SixtyEightPublishers\FixturesBundle\ObjectLoader\FindBy(
					SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\SequenceIdGenerator(places_*),
					App\Entity\Place
					[],
					null,
					50
				)
```

Here is a list of predefined loaders (it copies a basic methods from Doctrine's `ObjectRepository`):

- `SixtyEightPublishers\FixturesBundle\ObjectLoader\Find`
- `SixtyEightPublishers\FixturesBundle\ObjectLoader\FindAll`
- `SixtyEightPublishers\FixturesBundle\ObjectLoader\FindBy`
- `SixtyEightPublishers\FixturesBundle\ObjectLoader\FindOneBy`

And this is a list of predefined ID generators:

- `SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\SequenceIdGenerator`
- `SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\ListIdGenerator`
- `SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\ValueIdGenerator` - this generator is useful with loaders `Find` and `FindOneBy` only

Of course you can define a custom object loader by implementing an interface `SixtyEightPublishers\FixturesBundle\ObjectLoader\ObjectLoaderInterface`.

## Scenes

Internally each scenario consists of scenes. This configurations are both internally similar:

```neon
68publishers.fixtures_bundle:
	scenarios:
		default:
			purge_mode: no_purge
			fixtures:
				- my/fixture-1.neon
				- my/fixture-2.neon
			object_loaders:
				- App\Fixtures\MyObjectLoader
```

```neon
68publishers.fixtures_bundle:
	scenarios:
		default:
			purge_mode: no_purge
			scenes:
				default:
					fixtures:
						- my/fixture-1.neon
						- my/fixture-2.neon
					object_loaders:
						- App\Fixtures\MyObjectLoader
```

So the first configuration is just shorter way to define the `default` scene.
Each scene is run separately so scenes can't share objects between themselves. The storage (Doctrine's `ObjectManager`) is cleared after each scene so all entities are detached from EM when a scenario successfully finished.

### Batched scenes

This concept based on scenes provides you a way how to import a lot of objects in batches without memory leaks. Here is an example of a scenario that contains some basic scene and some batched scene:

```neon
68publishers.fixtures_bundle:
	scenarios:
		default:
			purge_mode: no_purge
			# the `default` scene is defined in shorter way
			fixtures:
				- base/users.neon
				- base/articles.neon
			scenes:
				product:
					decorator: SixtyEightPublishers\FixturesBundle\Scenario\Scene\BatchedScene(100) # a fixture `product/products.neon` will be loaded 100 times
					fixtures:
						- product/products.neon
					object_loaders:
						# users were created in the `default` scene but they are not accessible in this scene so we must load it:
						- SixtyEightPublishers\FixturesBundle\ObjectLoader\FindAll(SixtyEightPublishers\FixturesBundle\ObjectLoader\Id\SequenceIdGenerator(user_*), App\Entity\User)
```

An option `decorator` defines decorator class that wraps the original `Scene` object. The original object is passed into the decorator via the constructor's argument with the name `$scene`.

## Usage

Fixtures can be simply loaded with the following command:

```bash
$ php bin/console fixtures:load [<SCENARIO>] [--purge-mode <PURGE_MODE>] [--driver <DRIVER>]
```

The scenario with the name `default` is used if an argument `scenario` is not passed. A purge mode can be overridden with an option `--purge-mode` and a preferred driver can be set with an option `--driver`. 

All available fixtures and scenarios can be listed with the following command:

```bash
$ php bin/console fixtures:list [<SCENARIO>] [--format <FORMAT>]
```

All fixtures and scenarios are listed if an argument `scenario` is not provided. Otherwise, the only fixtures for the specified scenario are listed. An allowed values for an option `--format` are `table` (default) and `raw`.

## Application parameters in fixtures

Parameters defined in the application's configuration like

```neon
parameters:
	foo: foo
	bar: 3
```

are also accessible in your fixtures. Nested parameters are flattened with a dot notation. For example

```neon
parameters: 
	aws_config:
		region: eu-central-1
		user: user
		password: 123456
```

will be accessible in your fixtures with these keys (the prefix `app` is automatically added)

```neon
parameters: 
    app.aws_config.region: eu-central-1
    app.aws_config.user: user
    app.aws_config.password: 123456
```

## Fixtures defined by another Compiler Extension

The Fixtures Bundle provides an interface `SixtyEightPublishers\FixturesBundle\DI\IFixturesBundleContributor` that can be implemented by another Compiler Extensions. 
Then these extensions can define a path to own fixtures, Faker Providers, or Processors.

```php
namespace ProductBundle\DI;

use Nette\DI\CompilerExtension;
use ProductBundle\Faker\ProductFakeProvider;
use ProductBundle\Processor\ProductProcessor;
use SixtyEightPublishers\FixturesBundle\DI\Configuration;
use SixtyEightPublishers\FixturesBundle\DI\FixturesBundleContributorInterface;

final class ProductBundleExtension extends CompilerExtension implements FixturesBundleContributorInterface
{
    # loadConfiguration(), beforeCompile(), afterCompile()

    /**
     * {@inheritDoc}
     */
    public function contributeToFixturesBundle(Configuration $configuration) : void
    {
        $configuration->provideFixtures('product_bundle', __DIR__ . '/../fixtures'); # these fixtures will be accessible in scenarios under alias @product_bundle
        $configuration->addFakerProvider(ProductFakeProvider::class);
        $configuration->addProcessor(ProductProcessor::class);
    }
}
```
