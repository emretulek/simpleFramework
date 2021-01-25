<?php

namespace Core\Model;

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
 * @see QueryBuilder::update()
 * @method static int|array|bool update(array $columns)
 *  *  -------------------------------------------------------------------------
 * @see QueryBuilder::delete()
 * @method static int|array delete($columns = null, $param = null)
 * *   -------------------------------------------------------------------------
 * @see QueryBuilder::softDelete()
 * @method static int|array|bool softDelete($columns = null, $param = null)
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
 * -----------------------------------------------------------------------------
 * @see QueryBuilder::debug()
 * @method static QueryBuilder debug()
 */
abstract class Model
{
    protected App $app;
    protected static array $instance;
    protected string $table = "";
    protected string $pk = "";
    protected bool $softDelete = false;
    protected array $errors = [];

    public function __construct()
    {
        $this->app = App::getInstance();
    }

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
        $database = self::static()->app->resolve(Database::class);

        if (method_exists(QueryBuilder::class, $methods)) {

            $queryBuilder = $database->table(self::static()->table);

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
            throw new Exception('Method ' . $methods . ' not found in ' . get_class($database));
        }
    }


    /**
     * @param $message
     * @param string $key
     * @return void
     */
    final protected function setError($message, $key = '')
    {
        if ($key) {
            $this->errors[$key] = $message;
        } else {
            $this->errors[] = $message;
        }
    }

    /**
     * @return array
     */
    final public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    final public function getLastError(): string
    {
        return end($this->errors);
    }
}

