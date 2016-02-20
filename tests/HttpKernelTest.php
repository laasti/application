<?php

namespace Laasti\Application\Test;

use Laasti\Http\HttpKernel;
use Laasti\Peels\Http\HttpRunner;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class HttpKernelTest extends \PHPUnit_Framework_TestCase
{

    public function testHttpKernel()
    {
        $kernel = new HttpKernel(new HttpRunner([function($request, $response, $next) {return $response;}]));
        $this->setExpectedException('RuntimeException');
        $kernel->run(new ServerRequest, new TextResponse('Test'));
    }
}
