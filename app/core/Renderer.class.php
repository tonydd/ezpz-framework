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
     * Will display a message into the message zone
     * Messages are store in session, as a result messages are shared by all renderers
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
     * Returns true if there are messages to display
     * @return bool
     */
    public function hasMessages()
    {
        return count(SessionHelper::getValue('messages') ?? []) > 0;
    }

    /**
     * Returns all messages and destroys them
     * @return array|null
     */
    public function popMessages()
    {
        $messages = SessionHelper::getValue('messages') ?? [];
        SessionHelper::unsetValue('messages');
        return $messages;
    }

    /**
     * Assign a variable to pass to the template
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
     * Assign a variable which will be converted to JSON and passed to the Javascript
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
     * Set template to be used
     * @param string $template : Template name
     * @return Renderer $this
     */
    public function setTemplate($template)
    {
        try {
            if (!($path = $this->findView($template))) {
                throw new InvalidArgumentException("Template $template could not be found.");
            } else {
                $this->template = $template;
            }
        } catch (InvalidArgumentException $e) {
            Controller::error("View file for $template has not been found ...");
        } catch (Exception $e) {
            Controller::error($e->__toString());
        }

        return $this;
    }

    /**
     * Get current renderer template
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set page title
     * @param string $title
     * @return Renderer $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get page title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Shortcut to get homepage URL
     * @return string
     */
    public function homepage()
    {
        return Controller::getCurrentController()->getBaseUrl();
    }


    /**
     * Shortcut to URL builder, accessible from views
     * @param $controller
     * @param $action
     * @param array $parameters
     * @param bool $doNotEncode
     * @return string
     */
    public function buildUrl($controller, $action, $parameters = [], $doNotEncode = false)
    {
        return Url::build($controller, $action, $parameters, $doNotEncode = false);
    }

    /**
     * Returns core JS with core variables binding
     * @return bool|string
     */
    public function getCoreJS()
    {
        $coreJSFile     = Renderer::DEFAULT_JS_PATH . DIRECTORY_SEPARATOR . 'EZ.js';
        $coreJS         = file_get_contents($coreJSFile);

        $this->bindCoreJSVars($coreJS);

        return $coreJS;
    }

    /**
     * Bind core JS variables
     * @param $coreJS
     */
    private function bindCoreJSVars(&$coreJS)
    {

        $baseUrl        = Controller::getCurrentController()->getBaseUrl();
        $serviceWorker  = 'EZServiceWorker.js';

        $binding = [
            '%baseUrl%'             => $baseUrl,
            '%pDelimiter%'          => Controller::PARAM_DELIMITER,
            '%fDelimiter%'          => Controller::PARAM_FIELD_DELIMITER,
            '%encodedParamName%'    => Controller::PARAM_ENCODED,
            '%workerUrl%'           => $serviceWorker
        ];


        // -- Init base vars
        $coreJS = str_replace(
            array_keys($binding),
            array_values($binding),
            $coreJS
        );
    }

    /**
     * Returns EZ Core JS Url (static controller)
     * @return string
     */
    public function getCoreJSUrl()
    {
        return $this->buildUrl('static', 'coreJs');
    }

    /**
     * Get project Javascript code
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
            $js = Factory::getMinifier()->minifyJS($js);
            $cache->setValue('site_js', $js, CacheInterface::TTL_INFINITY);
        }

        return $js;
    }
    
    /**
     * Fetch all JS project files and assemble them in a PHP var, then returns it
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
     * Returns URL to get JavaScript Project code from Frontoffice
     * @return string
     */
    public function getJSUrl()
    {
        return $this->buildUrl('static', 'js');
    }

    /**
     * Get project CSS code
     * @return mixed|null|string
     */
    public function getCSS()
    {
        $cache = Factory::getCache();

        if (!Controller::isDevMode() && ($css = $cache->getValue('site_css')) !== null) {
            return $css;
        }

        $finalCSS = $this->getRawCss();

        if(!Controller::isDevMode()) {
            $finalCSS = Factory::getMinifier()->minifyCSS($finalCSS);
            $cache->setValue('site_css', $finalCSS, CacheInterface::TTL_INFINITY);
        }

        return $finalCSS;
    }

    /**
     * Fetch all CSS project files and assemble them in a PHP var, then returns it
     * @return string
     */
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
     * Returns URL to get JavaScript Project code from Frontoffice
     * @return string
     */
    public function getCSSUrl()
    {
        return $this->buildUrl('static','css');
    }

    /**
     * Final rendering step : builds the page and displays it by including necessary files
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
     * Returns a list of all JS project files
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
     * Returns a list of all CSS project files
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
     * Check if a view exists in project
     * @param $name
     * @param null $directory
     * @return bool|string
     */
    private function findView($name, $directory = null)
    {
        if (isset($this->_views[$name])) {
            return $this->_views[$name];
        }

        $directoryFirst = ROOTPATH . '/code/views';
        $directorySecond = ROOTPATH . '/app/views';
        $fs = Controller::getFS();

        // -- A folder has been specified, $name is a path
        if (Helper::stringContains($name, $fs::SEP)) {
            $subDirname         = dirname($name);
            $name               = basename($name);

            $directoryFirst     .= $fs::SEP . $subDirname;
            $directorySecond    .= $fs::SEP . $subDirname;
        }

        if ($directory === null) {
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

            $files = [];
            $dirs = [];

            // -- Scan dir and separate files from directories
            if ($fs->is_dir($directory)) {
                $scan = $fs->scandir($directory);

                foreach ($scan as $file) {
                    $thePath = $directory . $fs::SEP . $file;

                    if ($fs->is_dir($thePath)) {
                        $dirs[]     = $thePath;
                    } else {
                        $files[]    = $thePath;
                    }
                }

                // -- Scan files first
                foreach ($files as $file) {
                    if (basename($file) === $needle) {
                        $this->_views[$name] = $file;
                        return $this->_views[$name];
                    }
                }

                // -- Scan sub directories then
                foreach ($dirs as $dir) {
                    if (($path = $this->findView($name, $dir))) {
                        return $path;
                    }
                }
            }

            return false;
        }

    }

    /**
     * Include a (sub)view
     * @param string $view
     */
    private function _include($view, $passData = false)
    {
        try {
            if (!($path = $this->findView($view))) {
                throw new InvalidArgumentException("View $view could not be found.");
            }

            if (file_exists($path)) {
                if ($passData) {
                    $data = $this->parameters;
                }
                include $path;
            }
        } catch (InvalidArgumentException $e) {
            Controller::error("View file for $view has not been found ...");
        } catch (Exception $e) {
            Controller::error($e->__toString());
        }
    }
}