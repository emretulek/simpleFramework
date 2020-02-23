<?php

namespace Core\Log;

use Core\Database\Database;


class DatabaseLog Implements LogInterface
{

    protected $table = 'logger';

    /**
     * Log yazma işlemini gerçekleştirir
     *
     * @param $message
     * @param $data
     * @param $type
     * @return bool
     */
    public function writer($message, $data, $type)
    {
        return Database::insert(
            "insert into {$this->table} set `type` = ?, `message` = ?, `data` = ?",
            [$type, $message, $data]);
    }
}
