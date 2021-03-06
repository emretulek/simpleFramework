<?php

namespace Core\Model;

use Closure;
use Core\App;
use Core\Database\Database;
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
 * @method static int|array insert(array $columns)
 * *  -------------------------------------------------------------------------
 * @see QueryBuilder::multiLineInsert()
 * @method static array|int multiLineInsert(array $columns, $fraction = 1000)
 * *  -------------------------------------------------------------------------
 * @see QueryBuilder::upsert()
 * @method static array|int upsert(array $columns)
 * *  -------------------------------------------------------------------------
 * @see QueryBuilder::update()
 * @method static int|array|bool update($column, $param = false, bool $force = false)
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::delete()
 * @method static int|array delete($columns, bool $force = false)
 * *   -------------------------------------------------------------------------
 * @see QueryBuilder::softDelete()
 * @method static int|array|bool softDelete($columns)
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
 * @see QueryBuilder::isNotNull()
 * @method static QueryBuilder isNotNull(string $column, $andOR = 'AND')
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
 * @see QueryBuilder::append()
 * @method static QueryBuilder append(string $raw)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::prepend()
 * @method static QueryBuilder prepend(string $raw)
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
 * @method static mixed last(int $rowCount = 1)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::first()
 * @method static mixed first(int $rowCount = 1)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::transaction()
 * @method static bool transaction(Closure $callback)
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::beginTransaction()
 * @method static void beginTransaction()
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::rollBack()
 * @method static void rollBack()
 *  * -------------------------------------------------------------------------
 * @see QueryBuilder::commit()
 * @method static void commit()
 * -----------------------------------------------------------------------------
 * @see QueryBuilder::debug()
 * @method static QueryBuilder debug()
 */
abstract class Model
{
    protected static array $instance;
    protected string $table = "";
    protected string $pk = "";
    protected bool $softDelete = false;
    protected static array $errors = [];
    protected static bool $throw = false;


    /**
     * @return mixed|static
     */
    final public static function static(): self
    {
        return self::$instance[static::class] ?? self::$instance[static::class] = new static;
    }

    /**
     * @param $methods
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    final public static function __callStatic($methods, $arguments)
    {
        if (method_exists(QueryBuilder::class, $methods)) {

            $queryBuilder = App::getInstance()->resolve(Database::class)->table(self::static()->table);

            if (self::static()->table) {

                if (self::static()->pk) {
                    $queryBuilder->pk(self::static()->pk);
                }
            }

            if (self::static()->softDelete) {

                if ($methods == 'delete') {
                    return $queryBuilder->softDelete(...$arguments);
                } else {
                    $queryBuilder->isNull('deleted_at');
                }
            }

            return $queryBuilder->$methods(...$arguments);
        } else {
            throw new Exception('Method ' . $methods . ' not found in ' . static::class);
        }
    }


    /**
     * @param $message
     * @param string $key
     * @return false
     * @throws ModelException
     */
    final protected function setError($message, string $key = ''):bool
    {
        if ($key) {
            self::$errors[$key] = $message;
        } else {
            self::$errors[] = $message;
        }

        if(self::$throw){
            self::$throw = false;
            throw new ModelException($message);
        }

        return false;
    }

    /**
     * @return array
     */
    final public function getErrors(): array
    {
        $errors = self::$errors;
        self::$errors = [];
        return $errors;
    }

    /**
     * @return string
     */
    final public function getLastError(): ?string
    {
        return array_pop(self::$errors);
    }

    /**
     * @return $this
     */
    final public function throw():self
    {
        self::$throw = true;
        return $this;
    }
}

