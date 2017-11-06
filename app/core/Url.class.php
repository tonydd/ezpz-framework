<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 30/10/17
 * Time: 20:02
 */

class Url
{
    private $_useRewrite = false;

    private $_baseUrl = '';

    private static $_instance;

    public function __construct()
    {
        $this->_useRewrite  = (int)Conf::getValue('app/useWorker') === 1;
        $this->_baseUrl     = Controller::getInstance()->getBaseUrl();
    }

    /**
     * @param $controller
     * @param $action
     * @param array $parameters
     * @param bool $doNotEncode
     * @return string
     */
    public function buildUrl($controller, $action, $parameters, $doNotEncode = false)
    {
        return $this->_useRewrite && (int)Factory::getCache()->getValue('worker_register') === 1
            ? $this->buildWorkerUrl($controller, $action, $parameters, $doNotEncode)
            : $this->buildDefaultUrl($controller, $action, $parameters, $doNotEncode);
    }

    /**
     * @param $controller
     * @param $action
     * @param array $parameters
     * @param bool $doNotEncode
     * @return string
     */
    private function buildWorkerUrl($controller, $action, $parameters, $doNotEncode = false)
    {
        $url = $this->_baseUrl . DIRECTORY_SEPARATOR . 'ez' . DIRECTORY_SEPARATOR  . $controller . DIRECTORY_SEPARATOR . $action;

        if (!empty($parameters)) {
            if ($doNotEncode) {
                $i = 0;
                foreach ($parameters as $key => $value) {
                    $url .= ($i === 0 ? '?' : '&') . urlencode($key) . '=' . urlencode($value);
                    $i++;
                }
            }
            else {
                $url .= '?' . Controller::PARAM_ENCODED . '=' . $this->encodeParameters($parameters);
            }
        }

        return $url;
    }

    /**
     * @param $controller
     * @param $action
     * @param array $parameters
     * @param bool $doNotEncode
     * @return string
     */
    private function buildDefaultUrl($controller, $action, $parameters, $doNotEncode = false)
    {
        $url = $this->_baseUrl . DIRECTORY_SEPARATOR . '?ctl=' . $controller . '&action=' . $action;

        if (!empty($parameters)) {
            if ($doNotEncode) {
                foreach ($parameters as $key => $value) {
                    $url .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            else {
                $url .= '&' . Controller::PARAM_ENCODED . '=' . $this->encodeParameters($parameters);
            }
        }

        return $url;
    }

    /**
     * @param $params
     * @return string
     */
    private function encodeParameters($params)
    {
        $raw = '';

        foreach ($params as $key => $value) {
            $raw .= $key . Controller::PARAM_FIELD_DELIMITER . urlencode(json_encode($value)) . Controller::PARAM_DELIMITER;
        }

        $encoded = base64_encode(rtrim($raw, Controller::PARAM_DELIMITER));

        return $encoded;
    }

    /* STATIC */

    /**
     * @return Url
     */
    protected static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new Url();
        }

        return static::$_instance;
    }

    /**
     * @param $controller
     * @param $action
     * @param $parameters
     * @param bool $doNotEncode
     * @return string
     */
    public static function build($controller, $action, $parameters, $doNotEncode = false)
    {
        return static::getInstance()->buildUrl($controller, $action, $parameters, $doNotEncode = false);
    }
}