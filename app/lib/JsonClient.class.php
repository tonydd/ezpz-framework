<?php

class JsonClient
{
    const HTTP_STATUS_OK = 200;


    const DEFAULT_PAGE_SIZE = 30;
    const PAGE_SIZE_MAX = 150;

    const DEFAULT_SESSION_VALIDITY = 600;

    const LOGIN_FIELD = 'login';
    const PWD_FIELD = 'password';
    const TOKEN_FIELD = 'sessionid';

    /** @var String */
    protected $module;
    /** @var  String */
    protected $sessionId;
    /** @var  integer */
    protected $sessionValidity;
    /** @var  integer */
    private $sessionExpires;

    /** @var String  */
    protected $login;
    /** @var String  */
    protected $password;
    /** @var String */
    protected $baseUrl;

    /** @var  JsonApiParameterDefinition */
    protected $parameters;

    /** @var resource  */
    private $curl;

    /**
     * JsonApiClient constructor.
     * @param String $login
     * @param String $password
     * @param String $url
     * @param integer $curlConnectionTimeout
     * @param integer $curlTimeout
     */
    function __construct($login, $password, $url, $curlConnectionTimeout = 300, $curlTimeout = 600)
    {
        $this->login = $login;
        $this->password = $password;
        $this->baseUrl = $url;

        $this->setSessionValidity( self::DEFAULT_SESSION_VALIDITY );

        $this->curl = curl_init();
        $this->setCurlOpt( CURLOPT_SSL_VERIFYPEER, false);
        $this->setCurlOpt( CURLOPT_RETURNTRANSFER, true);
        $this->setCurlOpt( CURLOPT_CONNECTTIMEOUT ,$curlConnectionTimeout);
        $this->setCurlOpt( CURLOPT_TIMEOUT, $curlTimeout); //timeout in seconds
        set_time_limit(0);// to infinity for example
    }

    /**
     * JsonApiClient destructor.
     */
    function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @param String $sessId
     */
    public function setSessionId($sessId)
    {
        $this->sessionId = $sessId;
    }

    /**
     * @return String
     */
    public function getSessionId()
    {
        if ( !$this->isLogged()
            || (!is_null($this->sessionExpires) && time() > $this->sessionExpires))
        {
            $this->sessionExpires = time() + $this->getSessionValidity();
            $this->login();
        }
        return $this->sessionId;
    }

    /**
     * @return int
     */
    public function getSessionValidity()
    {
        return $this->sessionValidity;
    }

    /**
     * @param int $sessionValidity
     */
    public function setSessionValidity($sessionValidity)
    {
        $this->sessionValidity = $sessionValidity;
    }

    /**
     * @return int
     */
    protected function getSessionExpires()
    {
        return $this->sessionExpires;
    }

    /**
     * @param string $module
     */
    public function setCurrentModule($module)
    {
        if (is_null($module) || empty($module) || $module === '') {
            throw new InvalidArgumentException(__METHOD__ . ' $module parameter cannot be null or unset.');
        }

        $this->resetLinks(); // If module changes, cursor has to be reset
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getCurrentModule()
    {
        return $this->module;
    }

    /**
     * @return JsonApiParameterGroup|null
     */
    public function getCurrentParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $option
     * @param string $value
     */
    public function setCurlOpt($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Login to the Json API and store session ID inside class member
     */
    protected function login()
    {
        $this->addParameter(static::LOGIN_FIELD, $this->login);
        $this->addParameter(static::PWD_FIELD, $this->login);

        $response = $this->makeRequest( $this->prepareRequest('login', $this->getCurrentParameters(), false) );
        $this->sessionId = isset($response[static::TOKEN_FIELD]) ? $response[static::TOKEN_FIELD] : null;
    }

    /**
     * Fetches one chunk of data, according to the currentPageSize, and returns it.
     * Returns false if there is no chunk to retrieve
     * @return mixed|bool
     */
    public function find()
    {

        $parameters = $this->getCurrentParameters();
        if (!$parameters instanceof JsonApiParameterGroup)
        {
            $parameters = new JsonApiParameterGroup();
        }


        $url = $this->prepareRequest($this->getCurrentModule(), $parameters);
        $jsonResponse = $this->makeRequest($url);

        return $jsonResponse;
    }

    /**
     * Makes the actual HTTP request
     * @param String $url
     * @return null|mixed
     * @throws Exception
     */
    protected function makeRequest($url)
    {
        try  {
            // Set the url
            $this->setCurlOpt( CURLOPT_URL,$url);

            // Execute
            $response=curl_exec($this->curl);
        }
        catch (Exception $e) {
            throw $e;
        }

        $jsonResponse = $this->decode($response);

        return $jsonResponse;
    }

    /**
     * @param String $module
     * @param JsonApiParameterDefinition|null $parameters
     * @param boolean $token
     * @return string Final URL
     * Prepares the request <=> generates the final URL to target
     */
    private function prepareRequest($module, $parameters = null, $token = true)
    {
        /* MODULE */
        $url = $this->baseUrl . '/' . $module . '/';

        /* PARAMETERS */
        $operator = '?';

        if ($parameters instanceof JsonApiParameterDefinition)
        {
            if ($parameters instanceof JsonApiResource)
            {
                // When fetching a resource, its id must be passed inside the URL and not in GET parameters
                $url .= $parameters->toURLParameter();
            }
            else
            {
                $url .= '?' . $parameters->toURLParameter();
                $operator = '&';
            }
        }

        /* TOKEN */
        if ($token) $url .= $operator . 'sessionid=' . $this->getSessionId();

        return $url;
    }

    /**
     * Decode response from the API and validate its coherence
     * @param String $textResponse
     * @return array|mixed
     */
    protected function decode($textResponse)
    {
        if (is_null($textResponse) || !is_string($textResponse)) {
            throw new InvalidArgumentException("Parameter for " . __METHOD__ . " must be a string, $textResponse received.");
        }

        $jsonResponse = json_decode($textResponse, true);

        return $this->validateResponse($jsonResponse) ? $jsonResponse  : array();
    }

    /**
     * Validate the JSON response
     * @param $jsonResponse
     * @return bool
     * @throws Exception
     */
    protected function validateResponse($jsonResponse)
    {
        return true;
    }

    /**
     * Check if login has already been made
     * @return bool
     */
    private function isLogged()
    {
        return !is_null( $this->sessionId );
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addParameter($name, $value)
    {
        if (!$this->parameters instanceof JsonApiParameterGroup) {
            $this->parameters = new JsonApiParameterGroup();
        }

        $this->parameters->addParam(new JsonApiParameter($name, $value));
    }

    /**
     *
     */
    public function resetParameters()
    {
        $this->parameters = null;
    }
}

/**
 * Class JsonApiParameterDefinition
 * Common class to have a single type for every request parameter. Also defines the methdo that every sub-parameter should implement;
 */
abstract class JsonApiParameterDefinition
{
    public abstract function toURLParameter();
}

/**
 * Class JsonApiParameterGroup
 * Defines a group of parameters to make a query
 */
class JsonApiParameterGroup extends JsonApiParameterDefinition
{
    /** @var JsonApiParameter[] */
    protected $parameters = array();

    /** @var  JsonApiPage */
    protected $pagination;

    /**
     * @param JsonApiParameter $parameter
     */
    public function addParam(JsonApiParameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @return string
     */
    public function toURLParameter()
    {
        $out = '';
        foreach ($this->parameters as $parameter)
        {
            $out .= $parameter->toURLParameter() . '&';
        }

        if (!is_null($this->pagination)) $out .= $this->pagination->toURLParameter() . '&';

        return rtrim($out, '&');
    }
}

/**
 * Class JsonApiParameter
 * Defines a single query parameter by its name and its value
 */
class JsonApiParameter extends JsonApiParameterDefinition
{
    protected $field;
    protected $value;

    /**
     * JsonApiParameter constructor.
     * @param $field
     * @param $value
     */
    function __construct($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function toURLParameter()
    {
        return $this->field . '=' . urlencode($this->value);
    }
}