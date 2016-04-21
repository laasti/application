<?php

namespace Laasti\Core\Providers;

use League\BooBoo\Runner;
use League\Container\ServiceProvider\AbstractServiceProvider;


class BooBooProvider extends AbstractServiceProvider
{
    const CLASS_METHOD_EXTRACTOR = "/^(.+)::(.+)$/";

    protected $provides = [
        'error_handler',
        'League\BooBoo\Runner',
        'League\BooBoo\Formatter\HtmlTableFormatter',
        'League\BooBoo\Handler\LogHandler'
    ];

    protected $defaultConfig = [
        'pretty_page' => 'error_formatter',
        'exception_handlers' => [],
        //How errors are displayed in the output
        'formatters' => [
            'League\BooBoo\Formatter\HtmlTableFormatter' => E_ALL
        ],
        //How errors are handled (logging, sending e-mails...)
        'handlers' => [
            'League\BooBoo\Handler\LogHandler'
        ]
    ];

    public function register()
    {
        $di = $this->getContainer();
        $config = $this->getConfig();

        $di->add('League\BooBoo\Handler\LogHandler')->withArgument('Psr\Log\LoggerInterface');
        $di->add('League\BooBoo\Formatter\HtmlTableFormatter');

        if ($di->has('peels.exceptions')) {
            $di->add('error_formatter.kernel', 'Laasti\Http\HttpKernel')->withArgument('peels.exceptions');
            $args = ['error_formatter.kernel', 'peels.exceptions'];
        } else {
            $di->add('error_formatter.kernel', 'Laasti\Http\HttpKernel')->withArgument('error_formatter.callable');
            $args = ['error_formatter.kernel'];
        }

        $self = $this;
        $di->share('error_formatter', function($kernel, $runner, $request, $response) use ($config, $self) {
            $formatter = new \Laasti\Core\Exceptions\PrettyBooBooFormatter($kernel, $runner);
            foreach ($config['exception_handlers'] as $exceptionClass => $handler) {
                $formatter->setHandler($exceptionClass, $self->resolve($handler));
            }
            $formatter->setRequest($request)->setResponse($response);
            return $formatter;
        })->withArguments(array_merge($args, ['request', 'response']));

        $di->share('League\BooBoo\Runner', function() use ($di, $config) {
            $runner = new Runner();
            foreach ($config['formatters'] as $containerKey => $error_level) {
                $formatter = $di->get($containerKey);
                $formatter->setErrorLimit($error_level);
                $runner->pushFormatter($formatter);
            }
            foreach ($config['handlers'] as $containerKey) {
                $handler = $di->get($containerKey);
                $runner->pushHandler($handler);
            }
            if (isset($config['pretty_page'])) {
                $runner->setErrorPageFormatter($di->get($config['pretty_page']));
            }
            return $runner;
        });
        $di->add('error_handler', function() use ($di) {
            return [$di->get('League\BooBoo\Runner'),'register'];
        });
    }

    protected function getConfig()
    {
        $config = $this->getContainer()->get('config');
        if (isset($config['booboo']) && is_array($config['booboo'])) {
            $config = array_merge($this->defaultConfig, $config['booboo']);
        } else {
            $config = $this->defaultConfig;
        }

        return $config;
    }

    protected function resolve($callable)
    {
        $matches = [];
        if (is_string($callable) && preg_match(self::CLASS_METHOD_EXTRACTOR, $callable, $matches)) {
            list($matchedString, $class, $method) = $matches;
            if ($this->getContainer()->has($class)) {
                return [$this->getContainer()->get($class), $method];
            }
        } else if (is_string($callable) && $this->getContainer()->has($callable)) {
            return $this->getContainer()->get($callable);
        }

        if (is_callable($callable)) {
            return $callable;
        }
        throw new \InvalidArgumentException('Callable not resolvable: '.(is_object($callable) ? get_class($callable) : $callable));
    }
}
