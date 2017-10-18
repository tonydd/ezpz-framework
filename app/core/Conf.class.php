<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 09/10/17
 * Time: 21:10
 */

class Conf
{
    protected static $_conf;

    protected static function isLoaded()
    {
        return self::$_conf !== null;
    }

    protected static function loadConf()
    {
        $path = ROOTPATH . DIRECTORY_SEPARATOR . "conf.ini";

        // TODO test conf file

        self::$_conf = parse_ini_file($path, true);
    }

    /**
     *
     */
    public static function getValue($xPath)
    {
        if (!self::isLoaded()) {
            self::loadConf();
        }

        if (!Helper::stringContains($xPath, '/')) {
            return self::$_conf[$xPath] ?? null;
        }
        else {
            $parts = explode('/', $xPath);

            $sub = self::$_conf[$parts[0]] ?? [];

            unset($parts[0]);

            foreach ($parts as $part) {
                if (isset($sub[$part])) {
                    $sub = $sub[$part];
                }
                else {
                    return null;
                }
            }

            return $sub;
        }
    }

}