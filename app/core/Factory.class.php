<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 16:59
 */

class Factory
{
    public static function getRenderer()
    {
        $class = Conf::getValue('rewrite/renderer') ?? 'Renderer';
        return new $class();
    }

    /**
     * @return CacheInterface
     */
    public static function getCache()
    {
        $class = Conf::getValue('rewrite/cache') ?? 'Cache';
        return new $class();
    }

    public static function getMinifier()
    {
        $class = Conf::getValue('rewrite/minifier') ?? 'Minify';
        return new $class();
    }
}