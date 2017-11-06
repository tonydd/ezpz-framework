<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 19:05
 */

class Controller
{
    /** @var Controller[] */
    private static $_instances = array();

    /** @var  array */
    private static $_parameters;

    /** @var  string */
    private static $_currentCtlClass;

    /** @var  FS */
    private static $_fileSystemReflection;

    /** @var  string */
    private static $_defaultCtl;

    /** @var string  */
    const DEFAULT_ACTION = 'index';

    /** @var Renderer  */
    private $_renderer;

    /** @var  string */
    private $_baseUrl;

    /** @var string */
    const PARAM_DELIMITER = '|';

    /** @var string */
    const PARAM_FIELD_DELIMITER = '#';

    /** @var string */
    const PARAM_ENCODED = 'ep';

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->_renderer = Factory::getRenderer();
    }

    /**
     * Get all query parameters
     * @return array
     */
    public function getParameters()
    {
        return Controller::_getParameters();
    }

    /**
     * Get a single request parameter
     * @param string $name
     * @return mixed|null
     */
    public function getParameter(string $name)
    {
        $parameters = $this->getParameters();
        return $parameters[$name] ?? null;
    }

    /**
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = sprintf(
                "%s://%s",
                Conf::getValue('app/protocol') ?? 'http',
                Conf::getValue('app/baseUrl')
            );
        }
        return $this->_baseUrl;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

    /**
     *
     */
    public function getRequestedUrl()
    {
        return (Conf::getValue('app/protocol') ?? 'http')
            . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    
    /**
     * @param string $controller
     * @param string $action
     * @param array $parameters
     */
    public function redirect($controller, $action = Controller::DEFAULT_ACTION, $parameters = array())
    {
        //Controller::process(ucfirst($controller).'Controller', $action, $parameters);
        $targetUrl = $this->getRenderer()->buildUrl($controller, $action, $parameters);
        $this->redirectUrl($targetUrl);
    }

    /**
     *
     */
    public function redirectHome()
    {
        $targetUrl = $this->getBaseUrl();
        $this->redirectUrl($targetUrl);
    }

    /**
     *
     */
    public function redirectUrl($url)
    {
        $this->setheader('Location', $url);
    }

    /**
     * @return bool
     */
    protected function _beforeAction()
    {
        $this->_beforeAction = true;
        return true;
    }

    /**
     * @return bool
     */
    protected function _afterAction()
    {
        return true;
    }

    /**
     * @param $header
     * @param $value
     */
    public function setheader($header, $value)
    {
        header("$header: $value");
    }

    /* ----- ACTIONS */
    /**
     * The real default action, called if nothing has been found.
     * Congrats, you have reached the end of the internet.
     */
    protected function indexAction()
    {
        $this->getRenderer()->setTemplate('index');
        $this->getRenderer()->render();
    }

    /*
     *  CORE
     */
    /**
     * Static entry point
     * @param string $paramCtrl
     * @param string $paramAction
     * @param mixed $paramParameters
     * @return mixed
     */
    public static function process($paramCtrl = null, $paramAction = null, $paramParameters = null)
    {
        Controller::handleParameters($paramParameters);
        Controller::initFileSystem();

        $methodName = ($paramAction ?? Controller::getAction()) . 'Action';
        $ctlName    = $paramCtrl ?? Controller::getController();

        $targetCtl = Controller::getInstance($ctlName);
        Controller::$_currentCtlClass = $ctlName;


        if (method_exists($targetCtl, $methodName)) {
                $targetCtl->_beforeAction();
                $targetCtl->$methodName();
                $targetCtl->_afterAction();
                return 0;
        }
        else {
            return Controller::getInstance('static')->notFoundAction();
        }

        Controller::error("If you have reached this point, there is a problem with the provided couple controller/action.", E_USER_ERROR);
    }

    /**
     * Init File System Management
     */
    private static function initFileSystem()
    {
        static::$_fileSystemReflection = Factory::getFS();
    }

    /**
     *
     */
    private static function handleParameters($paramParameters)
    {
        // Handle parameters
        Controller::$_parameters = $paramParameters ?? Controller::getCleanedParameters();

        /*
         * Get base64 parameters
         */
        if (isset(Controller::$_parameters[Controller::PARAM_ENCODED])) {
            $rawData = base64_decode(Controller::$_parameters[Controller::PARAM_ENCODED]);
            $dataArray = [];

            foreach (explode(Controller::PARAM_DELIMITER, $rawData) as $component) {
                list($name, $value) = explode(Controller::PARAM_FIELD_DELIMITER, $component);
                $dataArray[$name] = json_decode(urldecode($value), true);
            }

            unset(Controller::$_parameters[Controller::PARAM_ENCODED]);
            Controller::$_parameters = array_merge(Controller::$_parameters, $dataArray);
        }
    }

    /**
     * @return array
     */
    private static function getCleanedParameters()
    {
        $get    = $_GET;
        $post   = $_POST;

        array_walk($get, ['Controller', 'cleanParamArr']);
        array_walk($post, ['Controller', 'cleanParamArr']);

        return array_merge($get, $post);
    }

    /**
     * @param $val
     * @param $index
     */
    private static function cleanParamArr(&$val, $index)
    {
        if ($index !== Controller::PARAM_ENCODED) {
            $val = urldecode($val);
        }
    }

    /**
     * @return array
     */
    public static function _getParameters()
    {
        return Controller::$_parameters;
    }

    /**
     * @return Controller
     */
    public static function getCurrentController()
    {
        return Controller::getInstance( Controller::$_currentCtlClass );
    }

    public static function error($message)
    {
        die('<h1>ERROR</h1><pre>'.$message.'</pre>');
    }

    public static function warn($message)
    {
        //echo '<pre>' . $message . '</pre>';
        Controller::getCurrentController()
            ->getRenderer()
            ->addMessage('<pre>' . $message . '</pre>', Renderer::WARN_MESSAGE);
    }

    /**
     * Get requested action
     * @return string
     */
    private static function getAction()
    {
        return isset(Controller::_getParameters()['action']) ? lcfirst(Controller::_getParameters()['action']) :  'index';
    }

    /**
     * @return string
     */
    private static function getController()
    {
        if (isset(Controller::_getParameters()['ctl'])) {
            return ucfirst(Controller::_getParameters()['ctl']) . 'Controller';
        }
        else {
            return Controller::getDefaultControllerClass();
        }
    }

    /**
     * @return string
     */
    private static function getDefaultControllerClass()
    {
        if (Controller::$_defaultCtl === null) {
            Controller::$_defaultCtl = Conf::getValue('app/mainCtl');
        }

        return Controller::$_defaultCtl;
    }

    /**
     * @return bool
     */
    public static function isDevMode()
    {
        return (int)Conf::getValue('app/dev') === 1;
    }

    /**
     * Returns file system manager
     * @return FS
     */
    public static function getFS()
    {
        return static::$_fileSystemReflection;
    }


    /**
     * Get Controller instance
     * @param string $ctl
     * @return Controller
     */
    public static function getInstance($ctl = null)
    {
        if ($ctl !== null) {
            $ctl = ucfirst($ctl);

            if (!Helper::stringContains($ctl, 'Controller')) {
                $ctl .= 'Controller';
            }
        }

        if ($ctl === null) {
            $ctl = Controller::getDefaultControllerClass();
        }

        if (!isset(Controller::$_instances[$ctl]) || Controller::$_instances[$ctl] === null) {
            Controller::$_instances[$ctl] = new $ctl();
        }

        return Controller::$_instances[$ctl];
    }
}