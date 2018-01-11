<?php

namespace JazzMan\Http;

use JazzMan\Http\Interfaces\RoutableInterface;
use JazzMan\Http\Traits\RouteShortcutsTrait;

/**
 * Class RouteGroup.
 */
class RouteGroup implements RoutableInterface
{
    
    use RouteShortcutsTrait;
    
    protected $router;
    
    protected $prefix;
    
    /**
     * RouteGroup constructor.
     *
     * @param string $prefix
     * @param        $router
     */
    public function __construct($prefix, Router $router)
    {
        
        $this->prefix = $prefix;
        $this->router = $router;
    }
    
    /**
     * @param string          $prefix
     * @param callable|string $callback
     *
     * @return $this
     */
    public function group($prefix, $callback)
    {
        
        $group = new self($this->appendPrefixToUri($prefix), $this->router);
        call_user_func($callback, $group);
        
        return $this;
    }
    
    /**
     * @param string          $verbs
     * @param string          $uri
     * @param callable|string $callback
     *
     *
     * @return Router
     */
    public function map($verbs = 'GET', $uri, $callback)
    {
        
        return $this->router->map($verbs, $this->appendPrefixToUri($uri), $callback);
    }
    
    /**
     * @param string $uri
     *
     * @return string
     */
    private function appendPrefixToUri($uri)
    {
        
        return $this->prefix . '/' . $uri;
    }
}