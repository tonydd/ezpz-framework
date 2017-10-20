<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 20/10/17
 * Time: 17:33
 */

class PDORequestDelete extends PDORequest
{
    public function from($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function toQueryString()
    {
        $sql = "DELETE ";

        // FROM
        $sql .= " FROM " . $this->quote( $this->tableName );

        // WHERE
        foreach ($this->clauses as $clause) {
            $sql .= $clause->toQueryString();
        }

        return $sql;
    }

    public function exec()
    {
        return $this->pdoh->exec($this->toQueryString());
    }

}