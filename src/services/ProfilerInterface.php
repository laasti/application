<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Services;

/**
 *
 * @author Sonia
 */
interface ProfilerInterface
{

    //put your code here
    public function openSection($name = null);

    public function stopSection($name);

    public function start($name);

    public function stop($name);

    public function getTimes();
}
