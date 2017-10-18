<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 12:05
 */

class PDORequestClause
{
    const COMP_DEFAULT_OP   = '=';

    const SEP_DEFAULT_OP    = '';
    const SEP_AND_OP        = 'AND';
    const SEP_OR_OP         = 'OR';

    private $conditions = [];

    /**
     * @param string|PDORequestClause $colOrClause
     * @param string $value
     * @param string $comp
     */
    public function where($colOrClause, $value = null, $comp = PDORequestClause::COMP_DEFAULT_OP, $cs = true)
    {
        if ($colOrClause instanceof PDORequestClause) {
            $this->conditions[] = $colOrClause;
        }

        $this->conditions[] = [
            'col'   => $colOrClause,
            'value' => $value,
            'comp'  => $comp,
            'sep'   => self::SEP_DEFAULT_OP,
            'cs'    => $cs
        ];

        return $this;
    }

    public function orWhere($colOrClause, $value = null, $comp = PDORequestClause::COMP_DEFAULT_OP, $cs = true)
    {
        if ($colOrClause instanceof PDORequestClause) {
            $this->conditions[] = $colOrClause;
        }

        $this->conditions[] = [
            'col'   => $colOrClause,
            'value' => $value,
            'comp'  => $comp,
            'sep'   => self::SEP_OR_OP,
            'cs'    => $cs
        ];

        return $this;
    }

    public function andWhere($colOrClause, $value = null, $comp = PDORequestClause::COMP_DEFAULT_OP, $cs = true)
    {
        if ($colOrClause instanceof PDORequestClause) {
            $this->conditions[] = $colOrClause;
            return $this;
        }

        $this->conditions[] = [
            'col'   => $colOrClause,
            'value' => $value,
            'comp'  => $comp,
            'sep'   => self::SEP_AND_OP,
            'cs'    => $cs
        ];

        return $this;
    }

    public function toQueryString()
    {
        $pdoh = PDOHelper::getInstance();
        $sql = '';

        foreach ($this->conditions as $conditionData) {
            if ($conditionData instanceof PDORequestClause) {
                $sql .= '(' . $conditionData->toQueryString() . ')';
            }
            else if (is_array($conditionData)) {
                $sql .= ' ';
                $sql .= ($conditionData['sep'] === '') ?  ' WHERE ' : ' ' . $conditionData['sep'] . ' ';

                if ($conditionData['cs']) {
                    $sql .= '`' . Helper::fromCamelCase($conditionData['col']) . '` ';
                }
                else {
                    $sql .= 'UPPER(`' . Helper::fromCamelCase($conditionData['col']) . '`) ';
                }

                $sql .= ($conditionData['comp'] ?? PDORequestClause::COMP_DEFAULT_OP) . ' ';
                if ($conditionData['cs']) {
                    $sql .= $pdoh->quote($conditionData['value']) . ' ';
                }
                else {
                    $sql .= $pdoh->quote(strtoupper($conditionData['value'])) . ' ';
                }
            }
            else {
                Controller::error("Condition unrecognized", E_USER_WARNING);
            }
        }

        return rtrim($sql, ' ');
    }
}