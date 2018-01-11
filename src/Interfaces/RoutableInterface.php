<?php

namespace JazzMan\Http\Interfaces;

use JazzMan\Http\Router;

/**
 * Interface Routable.
 */
interface RoutableInterface
{
    /**
     * @param string $verbs
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function map($verbs = 'GET', $uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function get($uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function post($uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function patch($uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function put($uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function delete($uri, $callback);
    
    /**
     * @param string $uri
     * @param        $callback
     *
     * @return Router
     */
    public function options($uri, $callback);
    
    /**
     * @param $prefix
     * @param $callback
     *
     * @return $this
     */
    public function group($prefix, $callback);
}