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
interface RouteCollectionInterface
{

    /**
     * Add a route to the collection
     *
     * @param  string                                   $method
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function addRoute($method, $route, $handler);

    /**
     * Returns the array of registered named routes (starting with @)
     *
     * @return array
     */
    public function getNamedRoutes();

    /**
     * Add a route that responds to GET HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function get($route, $handler);

    /**
     * Add a route that responds to POST HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function post($route, $handler);

    /**
     * Add a route that responds to PUT HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function put($route, $handler);

    /**
     * Add a route that responds to PATCH HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function patch($route, $handler);

    /**
     * Add a route that responds to DELETE HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function delete($route, $handler);

    /**
     * Add a route that responds to HEAD HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function head($route, $handler);

    /**
     * Add a route that responds to OPTIONS HTTP method
     *
     * @param  string                                   $route
     * @param  string|\Closure                          $handler
     * @return \Laasti\Services\RouteCollectionInterface
     */
    public function options($route, $handler);
}
