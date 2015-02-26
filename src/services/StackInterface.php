<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @author Sonia
 */
interface StackInterface
{

    public function unshift(MiddlewareInterface $obj);

    public function push(MiddlewareInterface $obj);

    /**
     *
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request);
}
