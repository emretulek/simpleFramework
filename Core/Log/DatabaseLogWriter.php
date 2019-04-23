<?php

namespace Core\Log;

use Core\Database\Database;


class DatabaseLogWriter Implements LogWriterInterface
{

    protected $table = 'log';

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
            "insert into {$this->table} set `message` = ?, `type` = ?, `data` = ?, `time` = NOW()",
            [$message, $type, $data]);
    }
}
