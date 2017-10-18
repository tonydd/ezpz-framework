<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 13:12
 */

class PDORequestInsert extends PDORequest
{
    protected $insertRows =   [];

    public function into($tableName)
    {
        $this->tableName = strtolower($tableName);

        return $this;
    }

    public function insertCols(array $cols = PDORequest::ALL_COLUMNS)
    {
        $this->columns = $cols;

        return $this;
    }
    
    public function insertRow(array $data)
    {
        $this->insertRows[] = $data;

        return $this;
    }

    public function toQueryString()
    {
        $sql = "INSERT INTO ";

        // TABLE
        $sql .=  $this->quote( $this->tableName ) . " ";

        // COLS
        $countToMatch = PHP_INT_MAX;
        if (!(count($this->columns) === 1 && $this->columns[0] === '*')) {
            array_walk($this->columns, [$this, 'quoteArray']);
            $sql .= '(' . implode(', ', $this->columns) . ') ';
            $countToMatch = count($this->columns);
        }

        $sql .= ' VALUES';

        foreach ($this->insertRows as $insertRow) {
            if (count($insertRow) < $countToMatch) {
                Controller::error("Values array didn't match number of columns to insert", E_USER_WARNING);
                continue;
            }

            array_walk($insertRow, [$this, 'quoteArrayValues']);

            $sql .= ' (' . implode(', ', $insertRow) . '),';
        }

        return rtrim($sql, ',');
    }

    public function exec()
    {
        return $this->pdoh->exec($this->toQueryString());
    }
}