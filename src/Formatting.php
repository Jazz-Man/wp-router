<?php

namespace JazzMan\Http;

/**
 * Class Formatting.
 */
class Formatting
{
    
    /**
     * @param string $input
     *
     * @return string
     */
    public static function addTrailingSlash($input)
    {
        
        return static::removeTrailingSlash($input) . '/';
    }
    
    /**
     * @param string $input
     *
     * @return string
     */
    public static function removeTrailingSlash($input)
    {
        
        return rtrim($input, '/\\');
    }
    
    /**
     * @param string $input
     *
     * @return string
     */
    public static function addLeadingSlash($input)
    {
        
        return '/' . static::removeLeadingSlash($input);
    }
    
    /**
     * @param string $input
     *
     * @return string
     */
    public static function removeLeadingSlash($input)
    {
        
        return ltrim($input, '/\\');
    }
}