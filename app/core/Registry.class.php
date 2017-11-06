<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 04/11/17
 * Time: 13:36
 */

class Registry
{
    private $_table = 'core_registry';

    private $_pdo;

    private $_cache;

    private static $_instance;

    public function __construct()
    {
        $this->_pdo = PDOHelper::getInstance();
        $this->_cache = Factory::getCache();
    }

    public function register($key, $value, $temporary = false)
    {
        $prev = $this->_getValue($key);
        $exists = $prev !== null && !empty($prev);

        if (!$exists) {
            if (is_array($value)) {
                $this->_registerArray($key, $value);
            }
            else {
                $this->_registerKey($key, $value);
            }

        } else {
            // TODO Update
        }
    }

    private function _registerKey($key, $value)
    {
        $this->_pdo->createInsert($this->_table)
            ->insertRow([$key, $value])
            ->exec();
    }

    private function _registerArray($key, $arrayValue)
    {
        foreach ($arrayValue as $subKey => $val) {
            $key = $key . '/' . $subKey;
            $this->_registerKey($key, $val);
        }
    }

    public function getValue($key) {
        if (($val = $this->_cache->getValue($key)) !== null) {
            return $val;
        }

        $val = $this->_getValue($key);
        $this->_cache->setValue($key, $val);
        return $val;
    }

    private function _getValue($key)
    {
        $parts = explode('/', $key);
        $cnt = count($parts);

        if ($cnt < 3) {
            $req = $this->_getRequest($key, true);
            $out = $req->findAssoc();
            return array_values(
                array_map('reset', $out)
            );
        }
        else if ($cnt === 3) {
            $req = $this->_getRequest($key, false);
            $out = $req->findAssoc();
            return $out[0]['value'] ?? null;
        }
        else {
            return null;
        }
    }

    private function _getRequest($key, $like = false)
    {
        $req = $this->_pdo->createSelect(['value'])
            ->from($this->_table);

        $clause = PDORequest::clause()->where(
            'key',
            $key . ($like ? '%' : ''),
            $like ? 'LIKE' : '=',
            !$like
        );

        $req->where($clause);

        return $req;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Registry();
        }

        return self::$_instance;
    }
}