<?php


namespace Laasti\Http;

use InvalidArgumentException;
use Laasti\Core\KernelInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpKernel implements HttpKernelInterface, KernelInterface
{
    protected $callable;
    protected $bufferSize;
    protected $emitter;

    public function __construct(callable $callable, EmitterInterface $emitter = null, $bufferSize = 1024)
    {
        $this->callable = $callable;
        $this->emitter = $emitter ?: new Emitter;
        $this->bufferSize = $bufferSize;
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if (!$request instanceof RequestInterface || !$response instanceof ResponseInterface) {
            throw new InvalidArgumentException("HttpKernel run method requires both instances of RequestInterface and ResponseInterface");
        }
        if (!is_callable($this->callable)) {
            throw new \InvalidArgumentException("HttpKernel does not have a valid callable.");
        }
        $this->emitter->emit(call_user_func_array($this->callable, [$request, $response]), $this->bufferSize);
    }

    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
        return $this;
    }

    public function setCallable(callable $runner)
    {
        $this->callable = $runner;
    }

    public function setEmitter($emitter)
    {
        $this->emitter = $emitter;
    }
}
