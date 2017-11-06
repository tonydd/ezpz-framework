<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 12:05
 */

abstract class PDORequest
{
    const ALL_COLUMNS = ['*'];

    const ORDER_DESC = 'DESC';
    const ORDER_ASC = 'ASC';

    /** @var array s */
    protected $columns = [];

    /** @var  string */
    protected $tableName;

    /** @var PDORequestClause[] */
    protected $clauses = [];

    /** @var PDOHelper  */
    protected $pdoh;


    public function __construct()
    {
        $this->pdoh = PDOHelper::getInstance();
    }

    abstract public function toQueryString();

    protected function quote($str)
    {
        return "`$str`";
    }

    protected function quoteArray(&$val)
    {
        $val = $this->quote($val);
        return true;
    }

    protected function quoteArrayValues(&$val)
    {
        $val = $this->pdoh->quote($val);
        return true;
    }

    /**
     * @return PDORequestClause
     */
    public function createClause()
    {
        return new PDORequestClause();
    }

    /**
     * @param PDORequestClause $clause
     * @return $this
     */
    public function where(PDORequestClause $clause)
    {
        $this->clauses[] = $clause;

        return $this;
    }

    /* STATIC */
    /**
     * @return PDORequestClause
     */
    public static function clause()
    {
        $dummyReq = new PDORequestInsert();
        return $dummyReq->createClause();
    }
}