<?php

namespace JazzMan\Http;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher\GroupCountBased as DefDispatcher;
use JazzMan\Http\Exceptions\NamedRouteNotFound;
use JazzMan\Http\Exceptions\RouteClassStringControllerNotFound;
use JazzMan\Http\Exceptions\RouteClassStringMethodNotFound;
use JazzMan\Http\Exceptions\RouteClassStringParse;
use JazzMan\Http\Exceptions\RouteNameRedefined;
use JazzMan\Http\Interfaces\RoutableInterface;
use JazzMan\Http\Interfaces\ServerResponseInterface;
use JazzMan\Http\Traits\RouteShortcutsTrait;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Router.
 */
class Router implements RoutableInterface
{
    use RouteShortcutsTrait;
    
    /**
     * @var Request
     */
    public $request;
    
    /**
     * @var \FastRoute\RouteCollector
     */
    private $collector;
    
    /**
     * @var mixed
     */
    private $action;
    
    /**
     * @var string
     */
    private $basePath;
    
    /**
     * @var array
     */
    private $named_route = [];
    
    /**
     * @var string
     */
    private $uri;
    
    /**
     * Router constructor.
     *
     * @param \FastRoute\RouteCollector|null $collector
     */
    public function __construct(RouteCollector $collector = null)
    {
        add_filter('do_parse_request', [$this, 'run'], 10, 2);
        
        $this->setBasePath('/');
        
        $this->collector = $collector ?: new RouteCollector(new Std(), new GroupCountBased());
        
        $this->request = Request::createFromGlobals();
    }
    
    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = Formatting::addLeadingSlash(Formatting::addTrailingSlash($basePath));
    }
    
    /**
     * @param string $name
     *
     * @return $this
     * @throws RouteNameRedefined
     */
    public function name($name)
    {
        if (empty($name)) {
            throw new RouteNameRedefined();
        }
        $this->named_route[$name] = $this->uri;
        
        return $this;
    }
    
    /**
     * @param string     $name
     * @param array|null $params
     *
     * @return string
     *
     * @throws \FastRoute\BadRouteException
     * @throws \JazzMan\Http\Exceptions\NamedRouteNotFound
     */
    public function url($name, array $params = [])
    {
        if (!isset($this->named_route[$name])) {
            throw new NamedRouteNotFound("Route '{$name}' does not exist.");
        }
        
        $url = '';
        
        $pattern = $this->named_route[$name];
        
        $routeParser = new Std();
        
        $routes = $routeParser->parse($pattern);
        
        $routes = array_last($routes);
        
        foreach ($routes as $part) {
            if (is_string($part)) {
                $url .= $part;
                continue;
            }
            
            if (is_array($part)) {
                $url .= !empty($params[$part[0]]) ?$params[$part[0]]: '';
                continue;
            }
        }
        
        return $url;
    }
    
    /**
     * @param string $prefix
     * @param        $callback
     *
     * @return Router
     */
    public function group($prefix, $callback)
    {
        $group = new RouteGroup($prefix, $this);
        
        call_user_func($callback, $group, $this->request);
        
        return $this;
    }
    
    /**
     * @param string          $verbs
     * @param string          $uri
     * @param callable|string $callback
     *
     * @return $this
     */
    public function map($verbs = 'GET', $uri, $callback)
    {
        $this->collector->addRoute($verbs, $uri, $callback);
        
        $this->uri = $uri;
        
        return $this;
    }
    
    /**
     * @param bool         $bool             Whether or not to parse the request. Default true.
     * @param \WP          $wp               current WordPress environment instance
     * @param array|string $extra_query_vars set the extra query variables
     *
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \JazzMan\Http\Exceptions\RouteClassStringControllerNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringMethodNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringParse
     */
    public function run($bool, \WP $wp, $extra_query_vars = '')
    {
        if ('do_parse_request' !== current_filter()) {
            return $bool;
        }
        
        $response = $this->match($this->request);
        
        if (404 !== $response->getStatusCode() && $response->send()) {
            exit();
        }
        
        return $bool;
    }
    
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     * @throws \JazzMan\Http\Exceptions\RouteClassStringControllerNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringMethodNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringParse
     */
    public function match(Request $request)
    {
        $dispatcher = new DefDispatcher($this->collector->getData());
        
        $uriPath = '/'.trim($request->getRequestUri(), '/');
        
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $uriPath ?: '/');
        
        if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        
        $this->setAction($routeInfo[1]);
        
        $params = new Request($routeInfo[2]);
        
        $returnValue = call_user_func($this->action, $params);
        // Ensure that we return an instance of a Response object
        if (!($returnValue instanceof Response)) {
            $returnValue = new Response(
              $returnValue,
              Response::HTTP_OK,
              ['content-type' => 'text/html']
            );
        }
        
        return $returnValue;
    }
    
    /**
     * @param callable|string $action
     *
     * @throws \JazzMan\Http\Exceptions\RouteClassStringControllerNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringMethodNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringParse
     */
    private function setAction($action)
    {
        // Check if this looks like it could be a class/method string
        if (!is_callable($action) && is_string($action)) {
            $action = static::convertClassStringToClosure($action);
        }
        $this->action = $action;
    }
    
    /**
     * @param $string
     *
     * @return \Closure
     * @throws \JazzMan\Http\Exceptions\RouteClassStringControllerNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringMethodNotFound
     * @throws \JazzMan\Http\Exceptions\RouteClassStringParse
     */
    private static function convertClassStringToClosure($string)
    {
        @list($className, $method) = explode('@', $string);
        if (!isset($className) || !isset($method)) {
            throw new RouteClassStringParse('Could not parse route controller from string: `'.$string.'`');
        }
        if (!class_exists($className)) {
            throw new RouteClassStringControllerNotFound('Could not find route controller class: `'.$className.'`');
        }
        if (!method_exists($className, $method)) {
            throw new RouteClassStringMethodNotFound('Route controller class: `'.$className.'` does not have a `'
                                                     .$method.'` method');
        }
        
        return function (Request $params = null) use ($className, $method) {
            $controller = new ReflectionClass($className);
            
            
            if ($controller->implementsInterface(ServerResponseInterface::class)) {
                $instance = $controller->newInstance();
                return $instance->$method($params);
            }
        };
    }
}