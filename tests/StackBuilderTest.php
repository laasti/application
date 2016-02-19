<?php

namespace Laasti\Application\Test;


class StackBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function testNoMiddlewares()
    {
        $this->setExpectedException('InvalidArgumentException');
        $builder = new \Laasti\Application\Middlewares\StackBuilder();
        $builder->create("Laasti\Application\Http\HttpRunner");
    }

    public function testNoRunner()
    {
        $this->setExpectedException('InvalidArgumentException');
        $builder = new \Laasti\Application\Middlewares\StackBuilder();
        $builder->create("");
    }

    public function testCreateDefaultRunner()
    {
        $builder = new \Laasti\Application\Middlewares\StackBuilder();
        $builder->push(function($x, $y, $next) {
            return $y;
        });
        $runner = $builder->create();

        $this->assertTrue($runner instanceof \Laasti\Application\Middlewares\IORunner);
    }

    public function testCreateMiddlewareInOrder()
    {
        $builder = new \Laasti\Application\Middlewares\StackBuilder();
        $builder->push(function($x, $y, $next) {
            return 3;
        });
        $builder->push(function($x, $y, $next) {
            return 2;
        });
        $runner = $builder->create();
        $this->assertTrue($runner(1,1) === 3);

        $builder->unshift(function($x, $y, $next) {
            return 4;
        });
        $runner = $builder->create();
        $this->assertTrue($runner(1,1) === 4);

    }

    public function testSetMiddlewares()
    {
        $builder = new \Laasti\Application\Middlewares\StackBuilder();
        $builder->push(function($x, $y, $next) {
            return 3;
        });
        $builder->setMiddlewares([
            function($x, $y, $next) {
                return 4;
            }
        ]);
        $runner = $builder->create();
        $this->assertTrue($runner(1,1) === 4);

    }
}
