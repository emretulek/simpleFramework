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
use Exception;


/**
 * @see QueryBuilder::pk()
 * @method static QueryBuilder pk($primaryColumn)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::table()
 * @method static QueryBuilder table(string $table, $overwrite = false)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::select()
 * @method static QueryBuilder select(string $select = "*", $overwrite = false)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::insert()
 * @method static QueryBuilder insert(array $columns)
 * *  -------------------------------------------------------------------------
 * @see QueryBuilder::update()
 * @method static QueryBuilder update(array $columns)
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::delete()
 * @method static QueryBuilder delete($columns = null, $param = null)
 * *   -------------------------------------------------------------------------
 * @see QueryBuilder::softDelete()
 * @method static QueryBuilder softDelete($columns = null, $param = null)
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::where()
 * @method static QueryBuilder where($column, $operant = null, $param = null)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::orWhere()
 * @method static QueryBuilder orWhere($column, $operant = null, $param = null)
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
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::find()
 * @method static mixed|array|bool find($param)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::last()
 * @method static array|bool last(int $rowCount = 1)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::first()
 * @method static array|bool first(int $rowCount = 1)
 */

class Model
{
    protected static array $instance;
    protected string $table = "";
    protected string $pk = "";
    protected bool $softDelete = false;
    protected array $errors = [];


    /**
     * @return mixed|static
     */
    public static function static()
    {
        return self::$instance[static::class] ?? self::$instance[static::class] = new static;
    }

    /**
     * @param $methods
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic($methods, $arguments)
    {
        if(method_exists(QueryBuilder::class, $methods)){

            $queryBuilder = new QueryBuilder();

            if(self::static()->table){
                $queryBuilder->table(self::static()->table);
            }

            if(self::static()->pk){
                $queryBuilder->pk(self::static()->pk);
            }

            if(self::static()->softDelete && $methods == 'delete'){

                return call_user_func_array([$queryBuilder, 'softDelete'], $arguments);
            }

            return call_user_func_array([$queryBuilder, $methods], $arguments);
        }else{
            throw new Exception('Method '.$methods.' not found in '.QueryBuilder::class);
        }
    }


    /**
     * @param $message
     * @param string $key
     * @return false
     */
    protected function setErrors($message, $key = '')
    {
        if($key){
            $this->errors[$key] = $message;
        }else{
            $this->errors[] = $message;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return end($this->errors);
    }
}

