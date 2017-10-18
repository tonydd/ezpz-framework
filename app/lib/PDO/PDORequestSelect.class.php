<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 12:23
 */

class PDORequestSelect extends PDORequest
{
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

    /**
     * @param PDORequestClause $clause
     * @return $this
     */
    public function where(PDORequestClause $clause)
    {
        $this->clauses[] = $clause;

        return $this;
    }


    public function orderBy($column, $sort = PDORequest::ORDER_ASC)
    {
        $this->order[] = [
            'col'   => $column,
            'sort'  => $sort
        ];

        return $this;
    }


    public function toQueryString()
    {
        $sql = "SELECT ";

        // SELECT
        //array_walk($this->columns, [$this, 'quoteArray']);
        $sql .= implode(', ', $this->columns);

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

        return $sql;
    }

    public function find()
    {
        return $this->pdoh->findInstances(Helper::toCamelCase($this->tableName), $this->toQueryString());
    }

    public function findOne()
    {
        return $this->pdoh->findInstance(Helper::toCamelCase($this->tableName), $this->toQueryString());
    }
}