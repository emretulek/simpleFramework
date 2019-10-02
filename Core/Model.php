<?php

namespace Core;

use Core\Database\Database as DB;

class Model
{
    protected static $instance;
    protected $table, $primary, $error;

    public static function static()
    {
        return isset(self::$instance[static::class]) ? self::$instance[static::class] : self::$instance[static::class] = new static;
    }

    public function getAll()
    {
        return DB::get("SELECT * FROM {$this->table}");
    }

    public function getFirst()
    {
        return DB::getRow("SELECT * FROM {$this->table} ORDER BY {$this->primary} ASC LIMIT 1");
    }

    public function getLast()
    {
        return DB::getRow("SELECT * FROM {$this->table} ORDER BY {$this->primary} DESC LIMIT 1");
    }

    public function find($primary)
    {
        return DB::getRow("SELECT * FROM {$this->table} WHERE  {$this->primary} = ?", [$primary]);
    }

    public function delete($primary)
    {
        return DB::delete("DELETE FROM {$this->table} where {$this->primary} = ?", [$primary]);
    }

    public function getError()
    {
        return $this->error;
    }
}

