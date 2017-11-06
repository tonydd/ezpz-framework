<?php

interface CacheInterface
{
    /**
     * @var int
     */
    const DEFAULT_TTL = 3600;

    /**
     * @var int
     */
    const TTL_INFINITY = -1;

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return mixed
     */
    public function setValue(string $key, $value, int $ttl = self::DEFAULT_TTL);

    /**
     * @param string $key
     * @return mixed
     */
    public function getValue(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function hasValue(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function clearValue(string $key);
}