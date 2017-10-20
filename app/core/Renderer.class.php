<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 19:15
 */

/**
 * Class Renderer
 */
class Renderer
{
    const VIEW_JS   = 'js';
    const VIEW_CSS  = 'css';
    const VIEW_HEAD = 'head';
    const VIEW_FOOT = 'foot';

    const DEFAULT_JS_PATH   = ROOTPATH . '/app/static/js';
    const DEFAULT_CSS_PATH  = ROOTPATH . '/app/static/css';

    /** @var  string */
    private $template;

    /** @var array  */
    private $parameters;

    /** @var  array */
    private $jsParameters;

    /** @var  array */
    private $_views;

    /** @var  string */
    private $title;

    const ERR_MESSAGE       = 'danger';
    const WARN_MESSAGE      = 'warning';
    const SUCCESS_MESSAGE   = 'success';
    const INFO_MESSAGE      = 'info';

    /**
     * Renderer constructor.
     */
    public function __construct()
    {
        $this->parameters = array();
        $this->_views = array();
    }

    /**
     * @param $message
     * @param $type
     * @return Renderer
     */
    public function addMessage($message, $type)
    {
        $messages = SessionHelper::getValue('messages') ?? [];

        $messages[] = [
            'label' => $message,
            'type'  => $type
        ];

        SessionHelper::setValue('messages', $messages);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return count(SessionHelper::getValue('messages') ?? []) > 0;
    }

    /**
     * @return array|null
     */
    public function popMessages()
    {
        $messages = SessionHelper::getValue('messages') ?? [];
        SessionHelper::unsetValue('messages');
        return $messages;
    }

    /**
     * @param string $name : Variable name
     * @param mixed $value : Value for name
     * @return Renderer $this
     */
    public function assign($name, $value)
    {
        if (isset($this->parameters[$name])) {
            array_merge($this->parameters[$name], $value);
        }
        else {
            $this->parameters[$name] = $value;
        }

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function assignJs($name, $value)
    {
        if (isset($this->parameters[$name])) {
            array_merge($this->jsParameters[$name], $value);
        }
        else {
            $this->jsParameters[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $template : Template name
     * @return Renderer $this
     */
    public function setTemplate($template)
    {
        if (!($path = $this->findView($template))) {
            throw new InvalidArgumentException("Template $template could not be found.");
        }
        else {
            $this->template = $template;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $title
     * @return Renderer $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function homepage()
    {
        return Controller::getCurrentController()->getBaseUrl();
    }

    /**
     * @param $controller
     * @param $action
     * @return string
     */
    public function buildUrl($controller, $action, $parameters = array(), $doEncode = true)
    {
        $url = Controller::getCurrentController()->getBaseUrl() . DIRECTORY_SEPARATOR . '?ctl=' . $controller . '&action=' . $action;

        if (!empty($parameters)) {
            if ($doEncode) {
                $url .= '&' . Controller::PARAM_ENCODED . '=' . $this->encodeParameters($parameters);
            }
            else {
                foreach ($parameters as $key => $value) {
                    $url .= '&' . urlencode($key) . '=' . urlencode($value);
                }
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
            $raw .= $key . Controller::PARAM_FIELD_DELIMITER . json_encode($value) . Controller::PARAM_DELIMITER;
        }

        $encoded = base64_encode(rtrim($raw, Controller::PARAM_DELIMITER));

        return $encoded;
    }

    /**
     * @return bool|string
     */
    public function getCoreJS()
    {
        $coreJSFile     = Renderer::DEFAULT_JS_PATH . DIRECTORY_SEPARATOR . 'EZ.js';
        $coreJS         = file_get_contents($coreJSFile);

        // -- Init base vars
        $coreJS = str_replace(
            [
                '%baseUrl%',
                '%pDelimiter%',
                '%fDelimiter%',
                '%encodedParamName%'
            ],
            [
                Controller::getCurrentController()->getBaseUrl(),
                Controller::PARAM_DELIMITER,
                Controller::PARAM_FIELD_DELIMITER,
                Controller::PARAM_ENCODED
            ],
            $coreJS
        );

        return $coreJS;
    }

    public function getCoreJSUrl()
    {
        return $this->buildUrl('static', 'coreJs');
    }

    /**
     * @return mixed|null|string
     */
    public function getJs()
    {
        $cache = Factory::getCache();

        if (!Controller::isDevMode() && ($js = $cache->getValue('site_js')) !== null) {
            return $js;
        }

        $js = $this->getRawJS();

        if(!Controller::isDevMode()) {
            $minifier = Factory::getMinifier();
            $js = $minifier->exec($js, 'js');
            $cache->setValue('site_js', $js, CacheInterface::TTL_INFINITY);
        }

        return $js;
    }
    
    /**
     * @return string
     */
    public function getRawJS()
    {
        $finalJS = '';
        $customJSPath = ROOTPATH . DIRECTORY_SEPARATOR . Conf::getValue("static/jsPath");

        $list = array_merge($this->getJSList(Renderer::DEFAULT_JS_PATH), $this->getJSList($customJSPath));

        foreach ($list as $jsFile) {
            $content = file_get_contents($jsFile);

            $finalJS .= '/*****************************************************************************' . PHP_EOL;
            $finalJS .= '***** ' . Helper::lastElem(explode(DIRECTORY_SEPARATOR, $jsFile)) . PHP_EOL;
            $finalJS .= '*****************************************************************************/' . PHP_EOL;

            $finalJS .= $content . PHP_EOL . PHP_EOL;
        }

        return $finalJS;
    }

    /**
     * @return string
     */
    public function getJSUrl()
    {
        return $this->buildUrl('static', 'js');
    }

    /**
     * @return string
     */
    public function getCSS()
    {
        $cache = Factory::getCache();

        if (!Controller::isDevMode() && ($css = $cache->getValue('site_css')) !== null) {
            return $css;
        }

        $finalCSS = $this->getRawCss();

        if(!Controller::isDevMode()) {
            $minifier = Factory::getMinifier();
            $finalCSS = $minifier->exec($finalCSS, 'css');
            $cache->setValue('site_css', $finalCSS, CacheInterface::TTL_INFINITY);
        }

        return $finalCSS;
    }

    public function getRawCss()
    {
        $finalCSS = '';
        $customCSSPath = ROOTPATH . DIRECTORY_SEPARATOR . Conf::getValue("static/cssPath");

        $list = array_merge($this->getCSSList(Renderer::DEFAULT_CSS_PATH), $this->getCSSList($customCSSPath));

        foreach ($list as $cssFile) {
            $content = file_get_contents($cssFile);

            $finalCSS .= '/*****************************************************************************' . PHP_EOL;
            $finalCSS .= '***** ' . Helper::lastElem(explode(DIRECTORY_SEPARATOR, $cssFile)) . PHP_EOL;
            $finalCSS .= '*****************************************************************************/' . PHP_EOL;

            $finalCSS .= $content . PHP_EOL . PHP_EOL;
        }

        return $finalCSS;
    }

    /**
     * @return string
     */
    public function getCSSUrl()
    {
        return $this->buildUrl('static','css');
    }

    /**
     *
     */
    public function render()
    {
        // -- HEAD
        $this->_include(Renderer::VIEW_HEAD);

        // -- Main
        $this->_include($this->getTemplate(), true);

        // -- Footer
        $this->_include(Renderer::VIEW_FOOT);
    }

    /*
     * Private internal functions
     */
    /**
     * @param null $directory
     * @return array
     */
    private function getJSList($directory = null)
    {
        $out = array();

        if ($directory === null) {
            $directory = ROOTPATH . '/js';
        }

        if (is_dir($directory)) {
            $scan = scandir($directory);
            unset($scan[0], $scan[1]); //unset . and ..
            foreach ($scan as $file) {
                if (is_dir($directory . "/" . $file)) {
                    array_merge($out, $this->getJSList($directory . "/" . $file));
                } else {
                    if ($file !== 'EZ.js') {
                        $out[] = "$directory/$file";
                    }
                }
            }
        }

        return $out;
    }

    /**
     * @param $directory
     * @return array
     */
    private function getCSSList($directory = null)
    {
        $out = array();

        if ($directory === null) {
            $directory = ROOTPATH . '/css';
        }

        if (is_dir($directory)) {
            $scan = scandir($directory);
            unset($scan[0], $scan[1]); //unset . and ..
            foreach ($scan as $file) {
                if (is_dir($directory . "/" . $file)) {
                    array_merge($out, $this->getCSSList($directory . "/" . $file));
                } else {
                    $out[] = "$directory/$file";
                }
            }
        }

        return $out;
    }


    /**
     * @param $name
     * @param null $directory
     * @return bool|string
     */
    private function findView($name, $directory = null)
    {
        if (isset($this->_views[$name])) {
            return $this->_views[$name];
        }

        if ($directory === null) {
            $directoryFirst = ROOTPATH . '/code/views';
            $directorySecond = ROOTPATH . '/app/views';

            if (($path = $this->findView($name, $directoryFirst))) {
                return $path;
            }
            else if (($path = $this->findView($name, $directorySecond))) {
                return $path;
            }
            else {
                return false;
            }
        }
        else {
            $needle = $name . '.php';

            if (is_dir($directory)) {
                $scan = scandir($directory);
                unset($scan[0], $scan[1]); //unset . and ..
                foreach ($scan as $file) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                        if (($path = $this->findView($name, $directory . DIRECTORY_SEPARATOR . $file))) {
                            return $path;
                        }
                    } else {
                        if ($file === $needle) {
                            $this->_views[$name] = $directory . DIRECTORY_SEPARATOR . $file;
                            return $this->_views[$name];
                        }
                    }
                }
            }

            return false;
        }

    }

    /**
     * @param string$view
     */
    private function _include($view, $passData = false)
    {
        if (!($path = $this->findView($view))) {
            throw new InvalidArgumentException("View $view could not be found.");
        }

        if (file_exists($path)) {
            if ($passData) {
                $data = $this->parameters;
            }
            include $path;
        }
    }
}