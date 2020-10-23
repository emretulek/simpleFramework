<?php
/**
 * @Created 21.10.2020 13:22:37
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Model
 * @package Core
 */

namespace Core\Model;

use Core\Database\DB;
use Core\Database\QueryBuilder;


/**
 * @see QueryBuilder::table()
 * @method static QueryBuilder table(string $table)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::select()
 * @method static QueryBuilder select(string $select = "*")
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::insert()
 * @method static QueryBuilder insert(array $columns)
 * *  -------------------------------------------------------------------------
 * @see QueryBuilder::update()
 * @method static QueryBuilder update(array $columns)
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::delete()
 * @method static QueryBuilder delete()
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::where()
 * @method static QueryBuilder where(string $column, string $operant, $param)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::orWhere()
 * @method static QueryBuilder orWhere(string $column, string $operant, $param)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::isNull()
 * @method static QueryBuilder isNull(string $column, $andOR = 'AND')
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::isNOTNull()
 * @method static QueryBuilder isNOTNull(string $column, $andOR = 'AND')
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::limit()
 * @method static QueryBuilder limit(int $length, int $start = 0)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::group()
 * @method static QueryBuilder group(string $column)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::order()
 * @method static QueryBuilder order(string $column, string $type = "ASC")
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::having()
 * @method static QueryBuilder having(string $column, string $operant, $param)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::orHaving()
 * @method static QueryBuilder orHaving(string $column, string $operant, $param)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::subQuery()
 * @method static QueryBuilder subQuery(callable $callback)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::join()
 * @method static QueryBuilder join(string $table, string $matching)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::leftJoin()
 * @method static QueryBuilder leftJoin(string $table, string $matching)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::rightJoin()
 * @method static QueryBuilder rightJoin(string $table, string $matching)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::cover()
 * @method static QueryBuilder cover($condition, $callback)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::buildQuery()
 * @method static string buildQuery()
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::bindingParams()
 * @method static array bindingParams()
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::getQueryType()
 * @method static string getQueryType()
 */

class Model
{
    protected static array $instance;
    protected string $table = "";
    protected string $pk = "";
    protected string $error = "";

    public DB $DB;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->DB = new DB;
    }

    /**
     * @return mixed|static
     */
    public static function static()
    {
        return isset(self::$instance[static::class]) ? self::$instance[static::class] : self::$instance[static::class] = new static;
    }

    /**
     * @param $methods
     * @param $arguments
     * @return mixed|null
     */
    public static function __callStatic($methods, $arguments)
    {
        if(method_exists(QueryBuilder::class, $methods)){

            return call_user_func_array([new QueryBuilder(), $methods], $arguments)->table(self::static()->table);
        }

        return null;
    }

    /**
     * @param $pk
     * @return mixed
     */
    public function find($pk)
    {
        if(is_array($pk)){
            $query = self::select();
            foreach ($pk as $col => $value){
                $query->where($col, '=', $value);
            }
            return $query->get();
        }else{
            return self::select()->where($this->pk, '=', $pk)->get();
        }
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return self::select()->order($this->pk)->limit(1)->get();
    }

    /**
     * @return mixed
     */
    public function last()
    {
        return self::select()->order($this->pk, 'DESC')->limit(1)->get();
    }

    /**
     * Delete primary key from model table
     * @param $pk
     * @return mixed
     */
    public function del($pk)
    {
        return self::delete()->where($this->pk, '=', $pk)->limit(1)->run();
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}

