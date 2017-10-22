<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 19:31
 */

use \PDOHelper as DB;

class Model
{
    /** ----- Class constants */
    const RELATION_ONE          = 1;
    const RELATION_MANY         = 2;
    const RELATION_MANY_MANY    = 3;
    const OP_AND                = 'AND';
    const OP_OR                 = 'OR';

    /* Define here table relations
        TO BE OVERRIDDEN IF NECESSARY
    */
    protected static $_relations    = array();

    /* Define here primary_key
        TO BE OVERRIDDEN IF NECESSARY
    */
    protected static $_pk  = 'id';

    /* Keep track of loaded relations for an instance */
    protected $_relationsLoaded     = array();

    /** @var bool  */
    protected static $_fixture = false;

    /*
     * Instances inherited methods
     */

    /**
     * Automatically handle getters and setters
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $class = get_class($this);

        if (Helper::stringStartsWith($name, 'get')) {
            // -- It's a getter
            $property = Helper::fromCamelCase( substr($name, 3) );


            if (property_exists($this, $property)) {
                // -- Check if it's a foreign key
                if (in_array($property, array_keys($class::$_relations))) {

                    // -- If so, check if it has been loaded already
                    if (!in_array($property, $this->_relationsLoaded)) {
                        $this->loadRelation($property, $class::$_relations[$property]);
                    }

                }

                return $this->{$property};
            }
        } else if (Helper::stringStartsWith($name, 'set')) {
            // -- It's a setter
            $property = Helper::fromCamelCase( substr($name, 3) );
            $data = $arguments[0];

            if (property_exists($this, $property)) {
                $this->{$property} = $data;
            }

            return $this;
        }
    }

    public function delete()
    {
        $methodPK = "get" . ucfirst(static::$_pk);
        $pk = $this->$methodPK();

        $req = DB::getInstance()->createDelete(Helper::fromCamelCase(get_called_class()));
        $clause = PDORequest::clause()->where(static::$_pk, $pk);
        $req->where($clause);

        $result = $req->exec();

        if ($result && static::$_fixture) {
            Fixture::dumpFixture(get_called_class());
        }

        return $result;
    }
    
    /**
     * @return bool
     */
    public function save()
    {
        if ($this->getId() === null) {
            // -- Creation
            $this->_insert();
        } else {
            // -- mise a jour
            $this->_update();
        }

        if (static::$_fixture) {
            Fixture::dumpFixture(get_called_class());
        }
    }

    private function _insert()
    {
        /** @var Model $class */
        $class = get_class($this);

        $db = DB::getInstance();
        $req = $db->createInsert($class);

        $columns = array();
        $values = array();

        foreach (get_class_vars($class) as $member => $dummy) {
            if (!Helper::stringStartsWith($member, '_') && !($member === $class::$_pk)) {

                if (isset($this::$_relations[$member])
                    && $this::$_relations[$member]['type'] === Model::RELATION_MANY) {
                    // TODO do it automatically
                    continue;
                }

                $columns[]          =  $member;

                $value = $this->{$member};
                if ($value instanceof Model) {
                    $value = $value->getId();
                }

                $values[] = $value;
            }
        }

        $req->insertCols($columns)->insertRow($values);

        return $req->exec();
    }

    private function _update()
    {

    }

    public function generateForm()
    {
        $renderer = Controller::getCurrentController()->getRenderer();

        /** @var Model $class */
        $class = get_class($this);

        $html = '<form method="post" action="' . $renderer->buildUrl($class, 'save') . '">';
        $html .= '<fieldset>';
        $html .= '<legend>' . $class . '</legend>';
        $html .= '<div class="form-container">';

        foreach (get_class_vars($class) as $member => $dummy) {
            if (!Helper::stringStartsWith($member, '_') && !($member === $class::$_pk)) {
                $html .= '<div class="form-group">';

                $value = $this->{$member};
                if ($value instanceof Model) {
                    $value = $value->getId();
                }

                $html .= "<label for='$member'>" . ucfirst($member) . " : </label>";
                $html .= "<input class='form-control' id='$member' name='$member' type='text' value='$value' />";
                $html .= "</div>";
            }

            if (!Helper::stringStartsWith($member, '_') && $member === $class::$_pk) {
                if (($pkVal = $this->getId()) !== null) {
                    $html .= "<input type='hidden' name='$member' id='$member' value='$pkVal' />";
                }
            }
        }

        $html .= '<button type="submit" class="btn btn-primary">Valider</button>';

        $html .= '</div>'; // End div.form-container
        $html .= '</fieldset>';
        $html .= '</form>';

        return $html;
    }

    /**
     * @param $property
     * @param $relationInfo
     */
    private function loadRelation($property, $relationInfo)
    {
        /** @var Model $className : Is in fact string name of class,
         *                      this snippet allows autocompletion for static fields*/
        if ($relationInfo['type'] === self::RELATION_ONE) {
            $className  = $relationInfo['class'];
            $relationId = $this->{$property};

            $this->{$property} = $className::load($relationId);
        }
        else if ($relationInfo['type'] === self::RELATION_MANY) {
            $className  = $relationInfo['class'];

            $this->{$property} = $className::loadByFields([
                [
                'field'     => $relationInfo['col'],
                'value'     => $this->getId()
                ]
            ]);
        }
    }

    public function setData(array $data)
    {
        foreach ($data as $field => $value) {
            $method = 'set' . ucfirst(Helper::toCamelCase($field));
            $this->$method($value);
        }

        return $this;
    }

    /*
     * STATIC METHODS
     */

    /**
     * Load by primary key
     * @param mixed $queryVal
     * @param bool $forceDbQuery
     * @return Model
     */
    public static function load($queryVal)
    {
        /** @var Model $className */
        $className = get_called_class();

        /** @var Cache $cache */
        $cache = Factory::getCache();
        $key = $className . '-' . $queryVal;

        if ($className::$_fixture) {
            return Fixture::loadAsFixture($className, $queryVal);
        }

        if (($data = $cache->getValue($key)) !== null) {
            return $data;
        }

        $found =  DB::getInstance()
            ->createSelect(PDORequest::ALL_COLUMNS)
            ->from(Helper::fromCamelCase($className))
            ->where(PDORequest::clause()->where(self::$_pk, $queryVal))
            ->findOne();

        $cache->setValue($key, $found);

        return $found;
    }

    /**
     * @param bool $forceDbQuery
     * @return Model[]
     */
    public static function loadAll()
    {
        $className = get_called_class();

        if ($className::$_fixture) {
            return Fixture::loadAllAsFixture($className);
        }

        return DB::getInstance()
            ->createSelect()
            ->from(Helper::fromCamelCase($className))
            ->find();
    }

    /**
     * @param array $conditions
     * @param string $operator
     * @param bool $forceDbQuery
     * @return Model[]
     */
    public static function loadByFields($conditions = array(), $operator = Model::OP_AND, $forceDbQuery = false)
    {
        $className = get_called_class();
        $db = DB::getInstance();

        $req = $db->createSelect()->from(Helper::fromCamelCase($className));
        $clause = PDORequest::clause();

        if (count($conditions)) {
            $index = 0;

            foreach ($conditions as $conditionInfo) {
                $compare = $conditionInfo['compare'] ?? PDORequestClause::COMP_DEFAULT_OP;
                $caseSensitive = $conditionInfo['case'] ?? true;

                if ($index === 0) {
                    $clause->where($conditionInfo['field'], $conditionInfo['value'], $compare, $caseSensitive);
                }
                else {
                    if ($operator === Model::OP_AND) {
                        $clause->andWhere($conditionInfo['field'], $conditionInfo['value'], $compare, $caseSensitive);
                    }
                    else {
                        $clause->orWhere($conditionInfo['field'], $conditionInfo['value'], $compare, $caseSensitive);
                    }
                }

                $index++;
            }

            $req->where($clause);
        }

        return $req->find();
    }
}