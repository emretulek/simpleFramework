<?php 
/**
 * @Created 19.10.2020 20:00:33
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Eloquent
 * @package Core\Database
 */

namespace Core\Database;


class MysqlQueryBuilder extends QueryBuilder {

    /**
     * @param int $length
     * @param int $start
     * @return $this
     */
    public function limit(int $length, int $start = 0):self
    {
        $this->limit = $start ?
            ' LIMIT ' . $length . ',' . $start :
            ' LIMIT ' . $length;

        return $this;
    }
}
