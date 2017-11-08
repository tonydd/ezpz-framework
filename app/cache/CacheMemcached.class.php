<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 06/11/17
 * Time: 19:06
 */

class CacheMemcached implements CacheInterface
{
    /** @var Memcached  */
    private static $client;

    /**
     * CacheMemcached constructor.
     */
    public function __construct()
    {
        if (!self::$client instanceof Memcached) {

            self::$client = new Memcached();
            self::$client->addServer(
                Conf::getValue('memcached/host'),
                Conf::getValue('memcached/port')
            );

        }
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function setValue(string $key, $value, int $ttl = self::DEFAULT_TTL)
    {
        return self::$client->set(
            $key,
            serialize($value),
            ($ttl !== self::TTL_INFINITY) ? $ttl : null
        );
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasValue(string $key)
    {
        return ($data = self::$client->get($key)) == Memcached::RES_NOTFOUND || !$data;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getValue(string $key)
    {
        return ($data = self::$client->get($key)) == Memcached::RES_NOTFOUND || !$data
            ? null
            : unserialize($data);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function clearValue(string $key)
    {
        return self::$client->delete($key);
    }

    public function getAllKeys()
    {
        return self::$client->fetchAll();
    }
}