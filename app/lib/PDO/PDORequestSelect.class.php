<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 12:23
 */

class PDORequestSelect extends PDORequest
{
    /** @var array  */
    protected $order = [];

    private $limit;

    private $offset;

    public function select($cols = PDORequest::ALL_COLUMNS)
    {
        $this->columns = $cols;

        return $this;
    }

    public function from($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function join($table)
    {

    }


    public function orderBy($column, $sort = PDORequest::ORDER_ASC)
    {
        $this->order[] = [
            'col'   => $column,
            'sort'  => $sort
        ];

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function toQueryString($count = false)
    {
        $sql = "SELECT ";

        // SELECT
        //array_walk($this->columns, [$this, 'quoteArray']);
        if ($count) {
            $sql .= " COUNT(*) AS 'cnt' ";
        } else {
            $sql .= implode(', ', $this->columns);
        }

        // FROM
        $sql .= " FROM " . $this->quote( $this->tableName );

        // WHERE
        foreach ($this->clauses as $clause) {
            $sql .= $clause->toQueryString();
        }

        // ORDER
        if (count($this->order)) {
            $sql .= " ORDER BY ";
            foreach ($this->order as $order) {
                $sql .= $this->quote($order['col']) . ' ' . $order['sort'] . ',';
            }

            $sql = rtrim($sql, ',');
        }

        // LIMIT
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        // OFFSET
        if ($this->limit !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        return $sql;
    }

    public function find()
    {
        return $this->pdoh->findInstances(ucfirst(Helper::toCamelCase($this->tableName)), $this->toQueryString());
    }

    public function findOne()
    {
        return $this->pdoh->findInstance(ucfirst(Helper::toCamelCase($this->tableName)), $this->toQueryString());
    }

    public function findAssoc()
    {
        return $this->pdoh->fetch($this->toQueryString());
    }

    public function count()
    {
        $find = $this->pdoh->fetch($this->toQueryString(true));
        if (!empty($find) && isset($find[0]['cnt'])) {
            return (int)$find[0]['cnt'];
        }

        return 0;
    }
}