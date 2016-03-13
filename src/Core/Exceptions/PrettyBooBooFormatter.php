<?php


namespace Laasti\Core\Exceptions;

use Exception;
use Laasti\Http\HttpKernel;
use League\BooBoo\Formatter\AbstractFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

class PrettyBooBooFormatter extends AbstractFormatter
{

    protected $handlers = [];
    protected $kernel;
    protected $request;
    protected $response;

    public function __construct(HttpKernel $kernel, $handlers = [])
    {
        $this->kernel = $kernel;
        $this->addHandlers($handlers);
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }
    
    public function format(Exception $e)
    {
        $callable = $this->getCallable($e);
        $this->kernel->setCallable($callable);
        $this->kernel->run($this->request->withAttribute('exception', $e), $this->response);
        exit;
    }

    public function setHandler($exceptionClass, $handler)
    {
        $this->handlers[$exceptionClass] = $handler;
        return $this;
    }

    public function addHandlers($handlers)
    {
        foreach ($handlers as $exceptionClass => $handler) {
            $this->setHandler($exceptionClass, $handler);
        }
        return $this;
    }

    protected function getCallable(Exception $e)
    {
        $class = get_class($e);
        if (isset($this->handlers[$class])) {
            return $this->handlers[$class];
        }

        $reflection = new ReflectionClass($e);

        foreach ($reflection->getInterfaceNames() as $interface) {
            if (isset($this->handlers[$interface])) {
                return $this->handlers[$interface];
            }
        }

        $parent = $reflection;
        while ($parent = $parent->getParentClass()) {
            if (isset($this->handlers[$parent->getName()])) {
                return $this->handlers[$parent->getName()];
            }
        }
        
        return $this->handlers['Exception'];
    }

}
