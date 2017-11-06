<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 16:59
 */

class Factory
{
    /**
     * @return Renderer
     */
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

    /**
     * @return Minifier
     */
    public static function getMinifier()
    {
        $class = Conf::getValue('rewrite/minifier') ?? 'Minify';
        return new $class();
    }

    /**
     * @return FS
     */
    public static function getFS()
    {
        if ((int)Conf::getValue('app/cachedFS') === 1) {
            return new CachedFileSystem();
        }

        return new DefaultFileSystem();
    }
}