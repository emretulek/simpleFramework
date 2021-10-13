<?php

namespace Core\Database;

class MysqlQueryBuilder extends QueryBuilder
{

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = ' LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this;
    }
}
