<?php


namespace Laasti\Http;

use InvalidArgumentException;
use Laasti\Core\KernelInterface;
use Laasti\Http\Emitter;
use Laasti\Http\EmitterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpKernel implements HttpKernelInterface, KernelInterface
{
    protected $runner;
    protected $bufferSize;
    protected $emitter;

    public function __construct($runner, EmitterInterface $emitter = null, $bufferSize = 1024)
    {
        $this->runner = $runner;
        $this->emitter = $emitter ?: new Emitter;
        $this->bufferSize = $bufferSize;
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if (!$request instanceof RequestInterface || !$response instanceof ResponseInterface) {
            throw new InvalidArgumentException("HttpKernel run method requires both instances of RequestInterface and ResponseInterface");
        }
        if (!is_callable($this->runner)) {
            throw new \InvalidArgumentException("HttpKernel's Runner is not callable.");
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
