<?php

namespace Laasti\Application\Test;

use Laasti\Application\Middlewares\IORunner;


class RunnerTest extends \PHPUnit_Framework_TestCase
{

    public function testNoMiddlewares()
    {
        $this->setExpectedException('InvalidArgumentException');
        new IORunner();
    }

    public function testIncompleteRunException()
    {
        $this->setExpectedException('Laasti\Application\Middlewares\IncompleteRunException');
        $runner = new IORunner([
            function ($x, $y, $this) {
                return $this($x, $y);
            }
        ]);
        $runner(3,5);

    }

    public function testBaseRunner()
    {
        $runner = new IORunner([
            function ($x, $y, $this) {
                return $y;
            }
        ]);
        $this->assertTrue($runner(10, 15) === 15);
    }

    public function testMultipleRunner()
    {
        $runner = new IORunner([
            function ($x, $y, $this) {
                $y++;
                return $this($x, $y);
            },
            function ($x, $y, $this) {
                $y++;
                return $y;
            }
        ]);
        $this->assertTrue($runner(1, 1) === 3);
    }

}
