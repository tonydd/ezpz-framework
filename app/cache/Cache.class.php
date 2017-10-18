<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 10/10/17
 * Time: 19:14
 */

class Cache implements CacheInterface
{
    /** @var  PDOHelper */
    private $_db;

    const TABLE     = 'core_cache';
    const KEY_COL   = 'key';
    const KEY_VAL   = 'value';
    const TTL_COL   = 'ttl';

    /**
     * @return PDOHelper
     */
    private function _getDb()
    {
        if ($this->_db === null) {
            $this->_db = PDOHelper::getInstance();
        }

        return $this->_db;
    }


    public function hasValue(string $key)
    {
        $req    = "SELECT count(*) AS count FROM " . self::TABLE . " WHERE `" . self::KEY_COL . '` = :target';
        $param  = [':target' => $key];

        $out    = $this->_getDb()->fetch($req, $param);
        $nb     = (int)$out[0]['count'];

        return $nb > 0;
    }

    public function getValue(string $key)
    {
        $req    = "SELECT `".self::KEY_VAL."`, `".self::TTL_COL."`  FROM " . self::TABLE . " WHERE `" . self::KEY_COL . '` = :target';
        $param  = [':target' => $key];
        $out = $this->_getDb()->fetch($req, $param);

        if (!empty($out)) {

            $cacheTtl = (int)$out[0][self::TTL_COL];
            if ($cacheTtl !== self::TTL_INFINITY && time() > $cacheTtl) {
                self::clearValue($key);
                return null;
            }

            return unserialize($out[0][self::KEY_VAL]);
        }

        return null;
    }

    public function setValue(string $key, $value, int $ttl = self::DEFAULT_TTL)
    {
        $ttl = time() + $ttl;

        $req = "INSERT INTO " . self::TABLE . " (`".self::KEY_COL."`, `".self::KEY_VAL."`, `".self::TTL_COL."`) VALUES (:ckey, :cval, :cttl)";
        $req .= " ON DUPLICATE KEY UPDATE `" . self::KEY_VAL . "` = :cval, `" . self::TTL_COL . "` = :cttl";

        $param = [
            ':ckey'     => $key,
            ':cval'     => serialize($value),
            ':cttl'     => $ttl
        ];

        $this->_getDb()->exec($req, $param);
    }

    public function clearValue(string $key)
    {
        $req    = "DELETE FROM " . self::TABLE . " WHERE `" . self::KEY_COL . "` = " . $this->_getDb()->quote($key);
        $this->_getDb()->exec($req);
    }
    
}