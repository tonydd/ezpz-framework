<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 18:26
 */

class PDOHelper
{

    /**
     * @var PDOHelper
     */
    private static $_instance;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * PDOHelper constructor.
     * @param string $dbUrl
     * @param string $dbUser
     * @param string $dbPasswd
     * @param string $dbName
     * @param string $dbPort
     */
    public function __construct($dbUrl, $dbUser, $dbPasswd, $dbName, $dbPort)
    {
        try {
            $this->pdo = new PDO("mysql:host=$dbUrl;port=$dbPort;dbname=$dbName", $dbUser, $dbPasswd);
        } catch (Exception $e) {
            Controller::error($e->__toString(), E_USER_ERROR);
        }
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function createSelect($cols = PDORequest::ALL_COLUMNS)
    {
        $q = new PDORequestSelect();
        $q->select($cols);
        return $q;
    }

    public function createInsert($table)
    {
        $q = new PDORequestInsert();
        $q->into($table);
        return $q;
    }

    public function createDelete($table)
    {
        $q = new PDORequestDelete();
        $q->from($table);
        return $q;
    }

    /**
     * @param $request
     * @param $parameters
     * @return array
     */
    public function fetch($request, $parameters = array())
    {
        $data = array();

        try {
            // -- Prepare request
            if (!($statement = $this->pdo->prepare($request))) {
                throw new Exception("Error preparing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Execute request
            if (!$statement->execute($parameters)) {
                throw new Exception("Error executing statement: " . var_export($statement->errorInfo(), true));
            }

           // -- Fetch data
            if (($data = $statement->fetchAll(PDO::FETCH_ASSOC)) === false) {
                throw new Exception("Error retrieving statement data: " . var_export($statement->errorInfo(), true));
            }

        }
        catch (Exception $e) {
            Controller::error($e->__toString(), E_USER_ERROR);
        }

        return $data;
    }

    /**
     * @param $request
     * @param $parameters
     * @return array
     */
    public function exec($request, $parameters = array())
    {
        try {
            // -- Prepare request
            if (!($statement = $this->pdo->prepare($request))) {
                throw new Exception("Error preparing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Execute request
            if (!$statement->execute($parameters)) {
                throw new Exception("Error executing statement: " . var_export($statement->errorInfo(), true));
            }

        }
        catch (Exception $e) {
            Controller::error($e->__toString(), E_USER_ERROR);
        }

        return true;
    }

    public function findInstance($className, $request, $parameters = array())
    {
        $instance = null;

        try {
            // -- Prepare request
            if (!($statement = $this->pdo->prepare($request))) {
                throw new Exception("Error preparing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Execute request
            if (!$statement->execute($parameters)) {
                throw new Exception("Error executing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Fetch data
            if (!$statement->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $className)) {
                throw new Exception("Error setting mode FETCH_CLASS : " . var_export($statement->errorInfo(), true));
            }

            if (!($instance = $statement->fetch())) {
                //throw new Exception("Error retrieving statement data: " . var_export($statement->errorInfo(), true));
                Controller::warn("PDOHelper couldn't create instance from request $request");
            }

        }
        catch (Exception $e) {
            Controller::error($e->__toString(), E_USER_ERROR);
        }

        return $instance;
    }

    public function findInstances($className, $request, $parameters = array())
    {
        $instances = array();

        try {
            // -- Prepare request
            if (!($statement = $this->pdo->prepare($request))) {
                throw new Exception("Error preparing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Execute request
            if (!$statement->execute($parameters)) {
                throw new Exception("Error executing statement: " . var_export($statement->errorInfo(), true));
            }

            // -- Fetch data
            if (!$statement->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $className)) {
                throw new Exception("Error setting mode FETCH_CLASS : " . var_export($statement->errorInfo(), true));
            }

            while ($instance = $statement->fetch()) {
                $instances[] = $instance;
            }

        }
        catch (Exception $e) {
            Controller::error($e->__toString(), E_USER_ERROR);
        }

        return $instances;
    }

    public function lastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param $str
     * @return string
     */
    public function quote($str)
    {
        return $this->pdo->quote($str);
    }

    /**
     * @param $str
     * @return string
     */
    public static function pdoQuote($str)
    {
        return PDOHelper::getInstance()->quote($str);
    }

    /**
     * @return PDOHelper
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {

            $dbUrl = Conf::getValue('database/url');
            $dbName = Conf::getValue('database/db');
            $dbUser = Conf::getValue('database/user');
            $dbPwd = Conf::getValue('database/password');
            $dbPort = Conf::getValue('database/port') ?? '3306';

            self::$_instance = new PDOHelper($dbUrl, $dbUser, $dbPwd, $dbName, $dbPort);
        }

        return self::$_instance;
    }
}