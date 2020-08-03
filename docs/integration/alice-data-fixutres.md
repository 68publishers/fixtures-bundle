# Integration of AliceDataFixtures

## AliceDataFixtures Documentation

For more information about the `AliceDataFixtures` package follows an official [documentation](https://github.com/theofidry/AliceDataFixtures/blob/master/README.md).

## Integration

Each supported Doctrine driver needs specific dependencies. Install them for the driver that you were chosen for your application:

```bash
# With Doctrine ORM
$ composer require doctrine/orm doctrine/data-fixtures

# With Doctrine ODM
$ composer require alcaeus/mongo-php-adapter doctrine/data-fixtures doctrine/mongodb-odm

# With Doctrine PHPCR
$ composer require theofidry/alice-data-fixtures doctrine/phpcr-odm jackalope/jackalope-doctrine-dbal
```

Of course you can use any of Doctrine integrations like [kdyby/doctrine](https://github.com/Kdyby/Doctrine) or [nettrine/orm](https://github.com/nettrine/orm).
Then register a compiler extension and enable drivers in the configuration:

```neon
extensions:
	fidry.alice_data_fixtures: SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\DI\FidryAliceDataFixturesExtension

# default configuration:
fidry.alice_data_fixtures:
	default_purge_mode: delete # 'delete', 'truncate' or 'no_purge'
	default_driver: doctrine_orm # 'doctrine_orm', 'doctrine_mongodb_odm' or 'doctrine_phpcr_odm'
	db_drivers:
		doctrine_orm: no
		doctrine_mongodb_odm: no
		doctrine_phpcr_odm: no
	event_listeners:
		allow_all: yes
		excluded: []
```

Integrations expects a service of type `ObjectManager` in your DI Container. For example, the driver `doctrine_orm` expects the existence of a service of type `@Doctrine\ORM\EntityManagerInterface`.

## Usage

Now you have access to enabled drivers and you can persist loaded fixtures:

```php
$provider = $container->getByType(SixtyEightPublishers\FixturesBundle\Bridge\AliceDataFixtures\Driver\IDriverProvider::class);
$loader = $container->getByType(Nelmio\Alice\FilesLoaderInterface::class);
$driver = $provider->getDriver('doctrine_orm');

$driver->load($loader->loadFiles([
    __DIR__ . '/users.neon',
    __DIR__ . '/articles.neon',
]));
```

## Processors

Processors allow you to process objects before and/or after they are persisted. Here is an example usage: 

```php
use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;

final class UserProcessor implements ProcessorInterface
{
    /**
     * {@inheritDoc}
     */
    public function preProcess(string $id, $object) : void
    {
        if (!$object instanceof User) {
            return;
        }

        # do whatever with user before persisting ...
    }
    /**
     * {@inheritDoc}
     */
    public function postProcess(string $id, $object) : void
    {
        if (!$object instanceof User) {
            return;
        }

        # do whatever with user after persisting ...
    }
}
```

```neon
services:
	-
		autowired: no
		type: Fidry\AliceDataFixtures\ProcessorInterface
		factory: UserProcessor
		tags:
			fidry_alice_data_fixtures.processor: yes
```

## Logger

The package using PSR Logger for logging information about loaded fixtures etc. 
If some service of type `Psr\Log\LoggerInterface` is accessible in the application's DI Container then this service will be used.

## Event Listener management

An event listeners or subscribers are an awesome feature but sometimes you don't wanna fire some event listeners when you are running fixtures.
For example, you have a listener that sends an email to a user after the entity is saved but you really won't to send email to the generated users.

So you can disable this listener/subscriber in the configuration:

```neon
fidry.alice_data_fixtures:
	event_listeners:
		excluded: 
			- App\Subscriber\SendEmailOnUserCreationSubscriber
```

Alternatively, if you have a lot of listeners/subscribers in your application you can disable all of them and keep enabled a few:

```neon
fidry.alice_data_fixtures:
	event_listeners:
		allow_all: no
		excluded: 
			- App\Subscriber\MyEventSubscriber
```
