<?php

namespace Laasti\Application\Test;

use Laasti\Application\Middlewares\MiddlewareResolver;


class MiddlewareResolverTest extends \PHPUnit_Framework_TestCase
{

    public static function staticMethod() {

    }

    public function publicMethod() {

    }

    public function __invoke()
    {
        ;
    }

    public function testCallables()
    {
        $resolver = new MiddlewareResolver();
        $this->assertTrue(is_callable($resolver->resolve(function() {})));
        $this->assertTrue(is_callable($resolver->resolve([$this, 'publicMethod'])));
        $this->assertTrue(is_callable($resolver->resolve([$this, 'staticMethod'])));
        $this->assertTrue(is_callable($resolver->resolve(["Laasti\Application\Test\MiddlewareResolverTest", 'staticMethod'])));
        $this->assertTrue(is_callable($resolver->resolve("Laasti\Application\Test\MiddlewareResolverTest::staticMethod")));
        $this->assertTrue(is_callable($resolver->resolve($this)));
    }

    public function testNonCallables()
    {
        $resolver = new MiddlewareResolver();
        $this->setExpectedException('InvalidArgumentException');
        $this->assertTrue(!is_callable($resolver->resolve("yo")));
    }

    public function testContainer()
    {
        $container = $this->getMock('Interop\Container\ContainerInterface');
        $container->expects($this->once())->method('has')->will($this->returnValue(true));
        $container->expects($this->once())->method('get')->will($this->returnValue(function() {}));
        $resolver = new MiddlewareResolver($container);
        $this->assertTrue(is_callable($resolver->resolve("MyTestContainer")));
    }

}
