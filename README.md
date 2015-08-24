# Laasti/application

The core of the Laasti framework.

Provides basic services that any apps need:

1. Dependency injection container using: league/container
2. Routing to controllers using: laasti/route (which uses league/route)
3. Request formatting and OO Response using: symfony/http-foundation
4. Application middlewares using: laasti/stack
5. Template rendering using: laasti/response
6. Logging using: monolog/monolog
7. Error handling using: league/booboo

## Installation

```
composer require laasti/lazydata
```

## Usage

```php
$config = [
    'routes' => [
        ['GET', '/welcome', 'MyControllerClass::welcome'],
    ],
];
$app = new Laasti\Application\Application();

$app->run(Symfony\Component\HttpFoundation\Request::create('/welcome')); //Outputs

```

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## History

See CHANGELOG.md for more information.

## Credits

Author: Sonia Marquette (@nebulousGirl)

## License

Released under the MIT License. See LICENSE.txt file.




