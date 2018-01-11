<?php

namespace JazzMan\Http\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ServerResponseInterface.
 */
interface ServerResponseInterface
{
    
    /**
     * @param Request $params
     *
     * @return mixed
     */
    public function index(Request $params);
}