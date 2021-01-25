<?php

namespace Core\Database;

class MysqlQueryBuilder extends QueryBuilder
{

    /**
     * @param int $length
     * @param int $start
     * @return $this
     */
    public function limit(int $length, int $start = 0): self
    {
        $this->limit = $start ?
            ' LIMIT ' . $length . ',' . $start :
            ' LIMIT ' . $length;

        return $this;
    }
}
