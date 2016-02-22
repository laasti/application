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

    protected $handlers;
    protected $kernel;
    protected $request;
    protected $response;

    public function __construct($handlers, HttpKernel $kernel, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->handlers = $handlers;
        $this->kernel = $kernel;
        $this->request = $request;
        $this->response = $response;
    }
    
    public function format(Exception $e)
    {
        $callable = $this->getCallable($e);
        $this->kernel->setRunner($callable);
        $this->kernel->run($this->request, $this->response);
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
