<?php

namespace Laasti\Application;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Application
{
    
    protected $container;
    protected $kernel;
    protected $logger;

    /**
     * Construction
     * @param Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->setErrorHandler();
    }
    
    public function getContainer()
    {
        return $this->container;
    }

    public function getKernel()
    {
        if (is_null($this->kernel)) {
            if ($this->container->has('kernel')) {
                $this->kernel = $this->container->get('kernel');
            } else if ($this->container->has('Laasti\Application\KernelInterface')) {
                $this->kernel = $this->container->get('Laasti\Application\KernelInterface');
            } else {
                throw new RuntimeException('No kernel found in the container. You must register using keyword "kernel" or full interface namespace.');
            }
        }
        
        return $this->kernel;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
                } else if ($this->getContainer()->has('Psr\Http\Message\RequestInterface')) {
                    $this->getContainer()->add('request', 'Psr\Http\Message\RequestInterface');           
                }
            }
            $request = $this->getContainer()->get('request');
        }
        if (is_null($response)) {
            if (!$this->getContainer()->has('response') && $this->getContainer()->has('Psr\Http\Message\ResponseInterface')) {
                $this->getContainer()->add('request', 'Psr\Http\Message\ServerRequestInterface');
            }
            $response = $this->getContainer()->get('response');
        }
        
        $this->getKernel()->run($request, $response);
        
        if ($this->getLogger()) {
            $this->getLogger()->debug('Script execution time: '.number_format(microtime(true) - $request->getServerParams()['REQUEST_TIME_FLOAT'], 3).' s');
            $this->getLogger()->debug('Memory usage: '.number_format(memory_get_usage()/1024/1024, 3).' MB.');
        }
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
            } else if ($this->getContainer()->has('Psr\Log\LoggerInterface')) {
                return $this->getContainer()->get('Psr\Log\LoggerInterface');
            }
        }
        
        return $this->logger;
    }
    
    
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    protected function setErrorHandler()
    {
        if ($this->getContainer()->has('error_handler')) {
            call_user_func($this->getContainer()->get('error_handler'));
        } else if (class_exists('League\BooBoo\Runner')) {
            $this->getContainer()->addServiceProvider('Laasti\Application\Providers\BooBooProvider');
            call_user_func($this->getContainer()->get('error_handler'));
        }
    }

}
