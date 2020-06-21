<?php

namespace Core\Log;

use Core\Database\DB;


class DatabaseLog Implements LogInterface
{

    protected string $table = 'logger';

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
        return DB::insert("insert into {$this->table} set `type` = ?, `message` = ?, `data` = ?",
            [$type, $message, $data]);
    }
}
