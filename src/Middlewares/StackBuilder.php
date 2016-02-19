<?php


namespace Laasti\Application\Middlewares;

class StackBuilder implements StackBuilderInterface
{

    protected $middlewares = [];
    protected $resolver;
    protected $runnerClass;

    public function __construct(MiddlewareResolverInterface $resolver, array $middlewares = [], $runnerClass = 'Laasti\Application\Middlewares\IORunner')
    {
        $this->setResolver($resolver ?: new MiddlewareResolver);
        $this->setMiddlewares($middlewares);
        $this->setRunnerClass($runnerClass);
    }

    public function setRunnerClass($runnerClass)
    {
        $this->runnerClass = $runnerClass;
        return $this;
    }
    

    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }


    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function push($middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function unshift($middleware)
    {
        array_unshift($this->middlewares, $middleware);
        return $this;
    }

    public function create($runnerClass = null)
    {
        $runnerClass = $runnerClass ?: $this->runnerClass;
        //TODO Seems quirky
        if (is_null($runnerClass) || !class_exists($runnerClass)) {
            throw new \InvalidArgumentException('StackBuilder needs a runner class.');
        }
        if (!count($this->middlewares)) {
            throw new \InvalidArgumentException('StackBuilder needs at least one middleware to create a runner.');
        }
        return new $runnerClass($this->middlewares);
    }
}
