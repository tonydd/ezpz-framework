<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 20:53
 */

class Helper
{
    /**
     * Checks if $haystack begins with $needle
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function stringStartsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * @param $str
     * @param $needle
     * @return bool
     */
    public static function stringContains($str, $needle)
    {
        if (strpos($str, $needle) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @return string
     */
    public static function fromCamelCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public static function toCamelCase(string $input) : string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    public static function lastElem($array)
    {
        return $array[count($array) - 1];
    }

    public static function getRenderedBlock($controller, $action)
    {

    }
}