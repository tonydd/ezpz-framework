<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 18:44
 */

session_start();

/**
 * Class SessionHelper
 */
class SessionHelper
{
    /**
     * @param $name
     * @param $value
     */
    public static function setValue($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @param $name
     * @return null
     */
    public static function getValue($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function valueExists($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * @param $name
     */
    public static function unsetValue($name)
    {
        unset($_SESSION[$name]);
    }
}