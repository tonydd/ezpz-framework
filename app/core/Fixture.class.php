<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 17:29
 */

class Fixture
{
    /** @var  CacheInterface */
    private static $_cache;

    /**
     * @return CacheInterface
     */
    private static function getCache()
    {
        if (self::$_cache === null) {
            self::$_cache = Factory::getCache();
        }

        return self::$_cache;
    }

    /**
     * @param $className
     * @param $queryVal
     * @return Model
     */
    public static function loadAsFixture($className, $queryVal)
    {
        if (!self::isFixtureLoaded($className)) {
            self::loadFixture($className);
        }

        $collection = self::getCache()->getValue('fixture-'.$className);
        return $collection[$queryVal] ?? null;
    }

    /**
     * @param $className
     * @return Model[]
     */
    public static function loadAllAsFixture($className)
    {
        if (!self::isFixtureLoaded($className)) {
            self::loadFixture($className);
        }

        return self::getCache()->getValue('fixture-'.$className);
    }

    /**
     * @param $className
     * @return bool
     */
    public static function isFixtureLoaded($className) {
        return self::getCache()->hasValue('fixture-'.$className);
    }

    /**
     * @param $className
     */
    public static function loadFixture($className)
    {
        $key = 'fixture-'.$className;
        if (!self::getCache()->hasValue($key)) {
            $data = array();

            $instances = PDOHelper::getInstance()
                ->createSelect()
                ->from(Helper::toCamelCase($className))
                ->find();

            foreach ($instances as $instance) {
                $data[$instance->getId()] = $instance;
            }

            self::getCache()->setValue($key, $data);
        }
    }

    public static function dumpFixture($className)
    {
        $key = 'fixture-'.$className;
        if (self::getCache()->hasValue($key)) {
            self::getCache()->clearValue($key);
        }
    }
}