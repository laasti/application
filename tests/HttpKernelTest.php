<?php

namespace Laasti\Application\Test;

use Laasti\Application\Http\HttpKernel;


class HttpKernelTest extends \PHPUnit_Framework_TestCase
{

    public function testHttpKernel()
    {
        $kernel = new HttpKernel(new \Laasti\Application\Http\HttpRunner([function($request, $response, $next) {return $response;}]));
        $this->setExpectedException('RuntimeException');
        $kernel->run(new \Zend\Diactoros\ServerRequest, new \Zend\Diactoros\Response\TextResponse('Test'));
    }
}
