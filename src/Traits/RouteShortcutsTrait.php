<?php

namespace JazzMan\Http\Traits;

use JazzMan\Http\Router;

trait RouteShortcutsTrait
{
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function get($uri, $callback)
    {
        
        return $this->map('GET', $uri, $callback);
    }
    
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function post($uri, $callback)
    {
        
        return $this->map('POST', $uri, $callback);
    }
    
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function patch($uri, $callback)
    {
        
        return $this->map('PATCH', $uri, $callback);
    }
    
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function put($uri, $callback)
    {
        
        return $this->map('PUT', $uri, $callback);
    }
    
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function delete($uri, $callback)
    {
        
        return $this->map('DELETE', $uri, $callback);
    }
    
    /**
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return Router
     */
    public function options($uri, $callback)
    {
        
        return $this->map('OPTIONS', $uri, $callback);
    }
}