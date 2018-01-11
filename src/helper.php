<?php

use JazzMan\Http\Router;

if (!function_exists('router')) {
    /**
     * @return \JazzMan\Http\Router
     */
    function router()
    {
        return new Router();
    }
}

if (!function_exists('route')) {
    /**
     * @param       $name
     * @param array $params
     *
     * @return string
     *
     * @throws \JazzMan\Http\Exceptions\NamedRouteNotFound
     */
    function route($name, array $params = [])
    {
        $url = router()->url($name, $params);

        return route_url($url);
    }
}

if (!function_exists('request')) {
    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    function request()
    {
        return router()->request;
    }
}

if (!function_exists('route_url')) {
    /**
     * @param string $base_url
     * @param array  $params
     *
     * @return string
     */
    function route_url($base_url, array $params = [])
    {
        $url = home_url($base_url);
        if (!empty($params) && is_array($params)) {
            $url = add_query_arg($params, $url);
        }

        return $url;
    }
}

if (!function_exists('current_url')) {
    /**
     * @return string
     */
    function current_url()
    {
        return request()->getUri();
    }
}
