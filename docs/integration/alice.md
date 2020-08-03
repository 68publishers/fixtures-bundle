# Integration of Alice

## Alice Documentation

For more information about the Alice and fixtures creating follows an official [documentation](https://github.com/nelmio/alice/blob/master/README.md).

## Integration

```neon
extensions:
	nelmio.alice: SixtyEightPublishers\FixturesBundle\Bridge\Alice\DI\NelmioAliceExtension

# default configuration:
nelmio.alice:
	locale: en_US
	seed: 1
	loading_limit: 5
	functions_blacklist: [current]
	max_unique_values_retry: 150
```

Now you can use these loaders for creating fixtures:

## Usage

```php
$fileLoader = $container->getByType(Nelmio\Alice\FileLoaderInterface::class);
$filesLoader = $container->getByType(Nelmio\Alice\FilesLoaderInterface::class);

$fixtures = $fileLoader->loadFile(__DIR__ . '/users.yaml');
#or
$fixtures = $filesLoader->loadFiles([
    __DIR__ . '/users.yaml',
    __DIR__ . '/articles.neon',
    __DIR__ . '/dummy-products.json'
]);
```

## Custom Faker Provider

Custom providers for [Faker Generator](https://github.com/fzaninotto/Faker) can be registered as tagged service. For example:

```php
final class AppProvider 
{
    public function concat(...$args) : string 
    {
        return implode('', $args);
    }
}
```

```neon
services:
	-
		autowired: no
		type: AppProvider
		tags:
			nelmio_alice.faker.provider: yes
```

The functions from the custom provider are now accessible in fixtures.

```neon
App\Entity\Dummy:
	john_doe:
		firstname: John
		lastname: Doe
		fullname: '<concat($firstname, " ", $lastname)>'
```

## Reflection Property Access

Object properties are read and written using getters and setters by default. However, if you want to use property access with a reflection as a fallback then override the following service:

```neon
services:
	nelmio_alice.property_accessor:
		autowired: no
		type: Symfony\Component\PropertyAccess\PropertyAccessorInterface
		factory: Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor(@nelmio_alice.property_accessor.std)
```

## Service factories

Alice already supports static factories but this integration adds the ability to use services from DIC as factories.

```neon
# config.neon
services:
	dummyEntityFactory: App\Entity\Factory\DummyEntityFactory

# fixtures/dummy.neon
App\Entity\Dummy:
	dummy_entity:
		__factory:
			'@dummyEntityFactory::create': [John, Doe] 
```

## Generate the fixtures in a specific order

Alice merges the fixtures from all files into one array and then are the objects generated from this common definition. 
Sometimes you need to generate objects in a specific order. In this case, you can redefine the DataLoader service:

```neon
services:
	nelmio_alice.data_loader:
		factory: SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\SortableDataLoader(
			@nelmio_alice.data_loader.simple,
			SixtyEightPublishers\FixturesBundle\Bridge\Alice\Loader\Processor\ClassPrioritySortableProcessor([
				App\Entity\Image: -10
			])
		)
```

In this example an `Image` entities will be always generated as last. A default priority is `0`.

## Dynamic parameters

Alice's documentation contains mentions about a [dynamic parameters](https://github.com/nelmio/alice/blob/master/doc/fixtures-refactoring.md#dynamic-parameters) but this functionality has been broken and it's not fixed yet.
With this integration you can use a simple workaround:

```neon
parameters:
	dummy_name_1: Foo
	dummy_name_2: Bar
	dummy_name_3: Baz

App\Entity\Dummy:
	'dummy_entity_{1..3}':
		name: '<parameter(dummy_name_<current()>)>'
```

This function also adds a syntax sugar - a nested parameters can be accessed with a dot notation. This behavior can be disabled via the second argument:

```neon
parameters:
	dummy:
		foo:
			name: Foo
			description: lorem ipsum ...
		bar:
			name: Bar
			description: lorem ipsum ...
	attribute.active: yes

App\Entity\Dummy:
	'dummy_entity_{foo, bar}':
		name: '<parameter(dummy.<current()>.name)>' # evaluates $parameters['dummy'][$current]['name']
		active: '<parameter(attribute.active, false)>' # evalueates $parameters['attribute.active']
```
