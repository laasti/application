<?php

namespace Laasti\Http;

use Interop\Container\ContainerInterface;
use Laasti\Core\ApplicationInterface;
use Laasti\Core\KernelInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Application implements ApplicationInterface
{

    protected $container;
    protected $kernel;
    protected $logger;

    /**
     * Construction
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);

        ini_set('max_execution_time', $this->getConfig('maxExecutionTime', 300));
        if (function_exists("set_time_limit") == true and @ini_get("safe_mode") == 0) {
            set_time_limit($this->getConfig('maxExecutionTime', 300));
        }
        date_default_timezone_set($this->getConfig('timezone', 'America/New_York'));

        error_reporting($this->getConfig('errorReporting', E_ALL | E_STRICT));
        ini_set('display_errors', $this->getConfig('displayErrors', true));
    }

    public function getConfig($key, $default = null)
    {
        return isset($this->getConfigArray()[$key]) ? $this->getConfigArray()[$key] : $default;
    }

    public function getConfigArray()
    {
        return $this->getContainer()->get('config');
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $container->share('container', $container);
        $container->share('Interop\Container\ContainerInterface', $container);
        return $this;
    }

    /**
     * Handles the request and delivers the response through the stack
     *
     * @param RequestInterface|null $request Request to process
     * @param ResponseInterface|null $response Response to process
     */
    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {

        if (is_null($request)) {
            if (!$this->getContainer()->has('request')) {
                if ($this->getContainer()->has('Psr\Http\Message\ServerRequestInterface')) {
                    $this->getContainer()->add('request', 'Psr\Http\Message\ServerRequestInterface');
                } elseif ($this->getContainer()->has('Psr\Http\Message\RequestInterface')) {
                    $this->getContainer()->add('request', 'Psr\Http\Message\RequestInterface');
                }
            }
            $request = $this->getContainer()->get('request');
        }
        if (is_null($response)) {
            if (!$this->getContainer()->has('response') && $this->getContainer()->has('Psr\Http\Message\ResponseInterface')) {
                $this->getContainer()->add('response', 'Psr\Http\Message\ResponseInterface');
            }
            $response = $this->getContainer()->get('response');
        }

        $this->getContainer()->add('request', $request);
        $this->getContainer()->add('response', $response);

        $this->setErrorHandler();
        $this->getKernel()->run($request, $response);

        if ($this->getLogger()) {
            if ($request instanceof ServerRequestInterface) {
                $this->getLogger()->debug('Script execution time: ' . number_format(microtime(true) - $request->getServerParams()['REQUEST_TIME_FLOAT'],
                        3) . ' s');
            }
            $this->getLogger()->debug('Memory usage: ' . number_format(memory_get_usage() / 1024 / 1024, 3) . ' MB.');
        }
    }

    protected function setErrorHandler()
    {
        if ($this->getContainer()->has('error_handler')) {
            call_user_func($this->getContainer()->get('error_handler'));
        } elseif (class_exists('League\BooBoo\Runner')) {
            $this->getContainer()->addServiceProvider('Laasti\Log\MonologProvider');
            $this->getContainer()->addServiceProvider('Laasti\Core\Providers\BooBooProvider');
            call_user_func($this->getContainer()->get('error_handler'));
        }
    }

    public function getKernel()
    {
        if (is_null($this->kernel)) {
            if ($this->container->has('kernel')) {
                $this->kernel = $this->container->get('kernel');
            } elseif ($this->container->has('Laasti\Http\HttpKernelInterface')) {
                $this->kernel = $this->container->get('Laasti\Http\HttpKernelInterface');
            } else {
                throw new RuntimeException('No kernel found in the container. You must register using keyword "kernel" or full interface namespace.');
            }
        }

        return $this->kernel;
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        return $this;
    }

    /**
     * Returns application logger
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            if ($this->getContainer()->has('logger')) {
                return $this->getContainer()->get('logger');
            } elseif ($this->getContainer()->has('Psr\Log\LoggerInterface')) {
                return $this->getContainer()->get('Psr\Log\LoggerInterface');
            } elseif (class_exists('Monolog\Logger')) {
                $this->getContainer()->addServiceProvider(new \Laasti\Log\MonologProvider);
                return $this->getContainer()->get('logger');
            }
        }

        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function setConfig($key, $value)
    {
        $config = $this->getConfigArray();
        $config[$key] = $value;
        //Arrays are not references, we need to push back our modification
        if (is_array($config)) {
            $this->getContainer()->add('config', $config);
        }
        return $this;
    }
}
