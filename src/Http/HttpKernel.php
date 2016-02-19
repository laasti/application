<?php


namespace Laasti\Application\Http;

use InvalidArgumentException;
use Laasti\Application\Http\HttpRunner;
use Laasti\Application\KernelInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

class HttpKernel implements KernelInterface
{
    protected $runner;
    protected $bufferSize;
    protected $emitter;

    public function __construct(HttpRunner $runner, EmitterInterface $emitter = null, $bufferSize = 1024)
    {
        $this->runner = $runner;
        $this->emitter = $emitter ?: new SapiEmitter;
        $this->bufferSize = $bufferSize;
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if (!$request instanceof RequestInterface || !$response instanceof ResponseInterface) {
            throw new InvalidArgumentException("HttpKernel run method requires both instances of RequestInterface and ResponseInterface");
        }
        $this->emitter->emit(call_user_func_array($this->runner, [$request, $response]), $this->bufferSize);
    }
    
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
        return $this;
    }

    public function setRunner($runner)
    {
        $this->runner = $runner;
    }

    public function setEmitter($emitter)
    {
        $this->emitter = $emitter;
    }
}
