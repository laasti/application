# Laasti/application

The core of the Laasti framework.

Maximizes flexibility with the use of standards and callables.

Provides basic services that any apps need:

## The core

### Dependency Injection with Container Interop

Reserved words: config, response, request, kernel, error_handler
Usually used with League/container

### Application configuration

* Multiple environments (develop, tests, staging, production...)
* An ArrayAccess implementation or plain array registered to the config container key

### Error handling (TODO)

* Reroute exceptions
* Handle exception by types
* Notify exceptions by type
* Manage errors

### Logging PSR3

## Input/Output

An abstract kernel implementation that takes an input and generate an output using middlewares.

### Request/Response

Http Kernel implementation takes a PSR7 ServerRequestInterface and outputs a PSR7 ResponseInterface.
The kernel accepts any callable as a runner, the callable will be passed a request and a response.

### ConsoleCommand/Result (TODO)

Console Kernel takes a command and displays the result

## Http Stuff, optional packages

### Psr7 Helpers and Cookie (TODO)

A cookie object that you can easily attach to your responses (```withHeader('Set-Cookie', (string) $cookie)```)

### Routing (TODO)

laasti/route: An elegant wrapper for nikic fast routes

### Session (TODO)

A simple session handler (defaults to native)

### Commands and command bus (TODO)

chief

### Database access (TODO)

DBAL

## Installation

```
composer require laasti/application
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




