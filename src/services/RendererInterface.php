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
interface RendererInterface
{
    public function render($template, $data = array());
    public function setVar($name, $data);
    public function setVars($variables = array());
    public function getVars();
    public function addLocation($path, $namespace);
    public function prependLocation($path, $namespace);
}
