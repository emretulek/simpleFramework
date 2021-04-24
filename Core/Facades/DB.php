<?php

namespace Core\Facades;

use Core\Database\Database;
use Core\Database\QueryBuilder;
use PDO;
use PDOStatement;

/**
 * -------------------------------------------------------------------------------------------
 * @see Database::selectDB()
 * @method static Database selectDB(string $database)
 * --------------------------------------------------------------------------------------------
 * @see Database::table()
 * @method static QueryBuilder table(string $table = '')
 * --------------------------------------------------------------------------------------------
 * @see Database::bindQuery()
 * @method static PDOStatement bindQuery($query, array $bindings = null, array $options = [])
 * --------------------------------------------------------------------------------------------
 * @see Database::get()
 * @method static array|bool get(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
 * --------------------------------------------------------------------------------------------
 * @see Database::getRow()
 * @method static bool|mixed getRow(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
 * --------------------------------------------------------------------------------------------
 * @see Database::getCol()
 * @method static array|bool getCol(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_COLUMN)
 * --------------------------------------------------------------------------------------------
 * @see Database::getVar()
 * @method static bool|mixed getVar(string $query, array $bindings = null)
 * --------------------------------------------------------------------------------------------
 * @see Database::insert()
 * @method static bool|string insert(string $query, array $bindings = null)
 * --------------------------------------------------------------------------------------------
 * @see Database::update()
 * @method static bool|int update(string $query, array $bindings = null)
 * --------------------------------------------------------------------------------------------
 * @see Database::delete()
 * @method static bool|int delete(string $query, array $bindings = null)
 * --------------------------------------------------------------------------------------------
 * @see Database::transaction()
 * @method static void transaction()
 * --------------------------------------------------------------------------------------------
 * @see Database::close()
 * @method static void close()
 * --------------------------------------------------------------------------------------------
 * @see Database::commit()
 * @method static void commit()
 * --------------------------------------------------------------------------------------------
 * @see Database::rollBack()
 * @method static void rollBack()
 * --------------------------------------------------------------------------------------------
 * @see Database::pdo()
 * @method static PDO pdo()
 * --------------------------------------------------------------------------------------------
 * @see Database::stm()
 * @method static PDOStatement stm()
 *--------------------------------------------------------------------------------------------
 */
class DB extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Database::class;
    }
}
