<?php

namespace Core\Database;

use Closure;
use Exception;
use InvalidArgumentException;
use PDO;

class QueryBuilder
{
    protected Database $database;
    protected array $params = [];
    protected string $table = '';
    protected string $select = '';
    protected string $insert = '';
    protected string $update = '';
    protected bool $delete = false;
    protected bool $force = false;
    protected string $where = '';
    protected string $group = '';
    protected string $having = '';
    protected string $order = '';
    protected string $limit = '';
    protected string $join = '';
    protected string $pk = '';
    protected int $paramCount = 0;
    protected string $append = '';
    protected string $prepend = '';
    protected string $raw = '';
    protected bool $debug = false;


    /**
     * QueryBuilder constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $primaryColumn
     * @return $this
     */
    public function pk(string $primaryColumn): self
    {
        $this->pk = $this->quoteColumn($primaryColumn);
        return $this;
    }

    /**
     * @param string $table
     * @param false $overwrite
     * @return $this
     */
    public function table(string $table, bool $overwrite = false): self
    {
        $table = $this->quoteColumn($table);

        if ($overwrite) {
            $this->table = $table;
        } else {
            $this->table .= $this->table ? ', ' . $table : $table;
        }

        return $this;
    }

    /**
     * @param string $select
     * @param false $overwrite
     * @return $this
     */
    public function select(string $select = "*", bool $overwrite = false): self
    {
        $select = $this->quoteColumn($select);

        if ($overwrite || $this->select == $select || $this->select == "*") {
            $this->select = $select;
        } else {
            $this->select .= $this->select ? ', ' . $select : $select;
        }

        return $this;
    }


    /**
     * @param $column
     * @param mixed $operant
     * @param mixed $param
     * @param string $andOR
     * @return $this
     */
    public function where($column, $operant = '=', $param = false, string $andOR = 'AND'): self
    {
        if (is_array($column)) {

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->where .= $this->where ? " $andOR " . $query : ' WHERE ' . $query;
            }

        } elseif ($param === false) {
            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? " $andOR " . $query : ' WHERE ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? " $andOR " . $query : ' WHERE ' . $query;
        }

        return $this;
    }


    /**
     * @param $column
     * @param mixed $operant
     * @param mixed $param
     * @return $this
     */
    public function orWhere($column, $operant = '=', $param = false): self
    {
        $this->where($column, $operant, $param, 'OR');

        return $this;
    }


    /**
     * @param $column
     * @param mixed $param
     * @param string $andOR
     * @return $this
     */
    public function like($column, $param = false, string $andOR = 'AND'): self
    {
        return $this->where($column, 'LIKE', $param, $andOR);
    }

    /**
     * @param $column
     * @param mixed $param
     * @param string $andOR
     * @return $this
     */
    public function notLike($column, $param = false, string $andOR = 'AND'): self
    {
        return $this->where($column, 'NOT LIKE', $param, $andOR);
    }

    /**
     * @param string $column
     * @param $param1
     * @param $param2
     * @param string $andOR
     * @return $this
     */
    public function between(string $column, $param1, $param2, string $andOR = 'AND'): self
    {
        if($rawParam1 = $this->paramToRaw($param1)){
            $param1 = $rawParam1;
        }

        if($rawParam2 = $this->paramToRaw($param2)){
            $param2 = $rawParam2;
        }

        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' BETWEEN ' . $param1 . ' AND ' . $param2
            : ' WHERE ' . $this->quoteColumn($column) . ' BETWEEN ' . $param1 . ' AND ' . $param2;

        return $this;
    }


    /**
     * @param string $column
     * @param $param1
     * @param $param2
     * @param string $andOR
     * @return $this
     */
    public function notBetween(string $column, $param1, $param2, string $andOR = 'AND'): self
    {
        if($rawParam1 = $this->paramToRaw($param1)){
            $param1 = $rawParam1;
        }

        if($rawParam2 = $this->paramToRaw($param2)){
            $param2 = $rawParam2;
        }

        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' NOT BETWEEN ' . $param1 . ' AND ' . $param2
            : ' WHERE ' . $this->quoteColumn($column) . ' NOT BETWEEN ' . $param1 . ' AND ' . $param2;

        return $this;
    }

    /**
     * @param string $column
     * @param array $params
     * @param string $andOR
     * @return $this
     */
    public function in(string $column, array $params, string $andOR = 'AND'): self
    {
        $questionMark = array_fill(0, count($params), '?');
        array_push($this->params, ...$params);

        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IN (' .implode(',', $questionMark). ')'
            : ' WHERE ' . $this->quoteColumn($column) . ' IN (' .implode(',', $questionMark). ') ';

        return $this;
    }


    /**
     * @param string $column
     * @param array $params
     * @param string $andOR
     * @return $this
     */
    public function notIn(string $column, array $params, string $andOR = 'AND'): self
    {
        $questionMark = array_fill(0, count($params), '?');
        array_push($this->params, ...$params);

        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' NOT IN (' .implode(',', $questionMark). ')'
            : ' WHERE ' . $this->quoteColumn($column) . ' NOT IN (' .implode(',', $questionMark). ') ';

        return $this;
    }


    /**
     * @param string $param1 tablo stun adı veya string değer
     * @param string|array $param2 tablo stun adı veya array
     * @param string $andOR AND|OR
     * @return $this
     */
    public function findInSet(string $param1, $param2, string $andOR = 'AND'): self
    {
        if(is_array($param2)){
            array_push($this->params, implode(',', $param2));
            $param2 = '?';
            $param1 = $this->quoteColumn($param1);
        }else{
            array_push($this->params, $param1);
            $param1 = '?';
            $param2 = $this->quoteColumn($param2);
        }


        $this->where .= $this->where
            ? ' ' . $andOR .  ' FIND_IN_SET ('.$param1.','.$param2.') '
            : ' WHERE ' . ' FIND_IN_SET ('.$param1.','.$param2.') ';

        return $this;
    }


    /**
     * @param string $param1 tablo stun adı veya string değer
     * @param string|array $param2 tablo stun adı veya array
     * @param string $andOR AND|OR
     * @return $this
     */
    public function notFindInSet(string $param1, $param2, string $andOR = 'AND'): self
    {
        if(is_array($param2)){
            array_push($this->params, implode(',', $param2));
            $param2 = '?';
            $param1 = $this->quoteColumn($param1);
        }else{
            array_push($this->params, $param1);
            $param1 = '?';
            $param2 = $this->quoteColumn($param2);
        }


        $this->where .= $this->where
            ? ' ' . $andOR .  ' NOT FIND_IN_SET ('.$param1.','.$param2.') '
            : ' WHERE ' . ' NOT FIND_IN_SET ('.$param1.','.$param2.') ';

        return $this;
    }

    /**
     * @param string $query
     * @param array $bindings
     * @param string $andOR
     * @return $this
     */
    public function exists(string $query, array $bindings = [], string $andOR = 'AND'):self
    {
        array_push($this->params, ...$bindings);

        $this->where .= $this->where
            ? ' ' . $andOR . ' EXISTS (' .$query. ')'
            : ' WHERE EXISTS (' .$query. ') ';

        return $this;
    }

    /**
     * @param string $query
     * @param array $bindings
     * @param string $andOR
     * @return $this
     */
    public function notExists(string $query, array $bindings = [], string $andOR = 'AND'):self
    {
        array_push($this->params, ...$bindings);

        $this->where .= $this->where
            ? ' ' . $andOR . ' NOT EXISTS (' .$query. ')'
            : ' WHERE NOT EXISTS (' .$query. ') ';

        return $this;
    }

    /**
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNull(string $column, string $andOR = 'AND'): self
    {
        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IS NULL '
            : ' WHERE ' . $this->quoteColumn($column) . ' IS NULL ';

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNotNull(string $column, string $andOR = 'AND'): self
    {
        $this->where .= $this->where
            ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IS NOT NULL '
            : ' WHERE ' . $this->quoteColumn($column) . ' IS NOT NULL ';

        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = ' LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this;
    }


    /**
     * @param string $column
     * @return $this
     */
    public function group(string $column): self
    {
        $this->group .= $this->group ?
            ', ' . $this->quoteColumn($column) :
            ' GROUP BY ' . $this->quoteColumn($column);

        return $this;
    }


    /**
     * @param string $column
     * @param string $type
     * @return $this
     */
    public function order(string $column, string $type = "ASC"): self
    {
        $type = strtoupper($type);

        if (!in_array($type, ['ASC', 'DESC'])) {
            $type = "ASC";
        }

        if ($column) {
            $this->order .= $this->order ?
                ', ' . $this->quoteColumn($column) . ' ' . $type :
                ' ORDER BY ' . $this->quoteColumn($column) . ' ' . $type;
        } else {
            $this->order = "";
        }

        return $this;
    }


    /**
     * @param int $limit
     * @return $this
     */
    public function rand(int $limit): self
    {
        $this->order .= $this->order ? ', RAND()' : ' ORDER BY RAND()';
        $this->limit($limit);

        return $this;
    }


    /**
     * @param $column
     * @param mixed $operant
     * @param mixed $param
     * @param string $andOr
     * @return $this
     */
    public function having($column, $operant = '=', $param = false, string $andOr = 'AND'): self
    {
        if (is_array($column)) {

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->having .= $this->having ? " $andOr " . $query : ' HAVING ' . $query;
            }

        } elseif ($param === false) {
            $query = $this->comparison($column, '=', $operant);
            $this->having .= $this->having ? " $andOr " . $query : ' HAVING ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->having .= $this->having ? " $andOr " . $query : ' HAVING ' . $query;
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function orHaving(string $column, string $operant, $param): self
    {
        $this->having($column, $operant, $param, 'OR');

        return $this;
    }


    /**
     * @param callable $callback function(QueryBuilder $qyery):string query
     * @return string
     * @throws Exception
     */
    public function subQuery(callable $callback): string
    {
        $queryBuilder = new QueryBuilder($this->database);
        $callback($queryBuilder);
        $query = '{{(' . $queryBuilder->buildQuery() . ')}}';

        foreach ($queryBuilder->bindingParams() as $param) {
            $paramName = $this->newParamName();
            $query = preg_replace("/:param_[0-9]/", $paramName, $query, 1);
            $this->params[$paramName] = $param;
        }

        return $query;
    }


    /**
     * @param string $table
     * @param string $matching t1.column = t2.column
     * @return $this
     */
    public function join(string $table, string $matching): self
    {
        $this->join .= ' JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching t1.column = t2.column
     * @return $this
     */
    public function leftJoin(string $table, string $matching): self
    {
        $this->join .= ' LEFT JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching t1.column = t2.column
     * @return $this
     */
    public function rightJoin(string $table, string $matching): self
    {
        $this->join .= ' RIGHT JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }


    /**
     * @param $condition
     * @param callable $callback function(QueryBuilder $qyery):void0
     * @return $this
     */
    public function cover($condition, callable $callback): self
    {
        if (strcasecmp($condition, 'AND') === 0 || strcasecmp($condition, 'OR') === 0 || strcasecmp($condition, 'WHERE') === 0) {
            $this->where .= $this->where ? ' ' . $condition . ' (' : ' WHERE (';
            call_user_func($callback, $this);
            $this->where = str_replace('( AND ', '(', $this->where);
            $this->where = str_replace('( OR ', '(', $this->where);
            $this->where .= ')';
        }

        return $this;
    }


    /**
     * @param string $raw
     * @param array $bindings
     * @return $this
     */
    public function raw(string $raw, array $bindings = []): self
    {
        $this->raw = $raw;
        array_push($this->params, ...$bindings);

        return $this;
    }


    /**
     * sorgu sonuna ekleme yapar
     * @param string $raw
     * @param array $bindings
     * @return $this
     */
    public function append(string $raw, array $bindings = []): self
    {
        $this->append = ' ' . $raw;
        array_unshift($this->params, ...$bindings);

        return $this;
    }


    /**
     * sorgu sonuna ekleme
     * @param string $raw
     * @param array $bindings
     * @return $this
     */
    public function prepend(string $raw, array $bindings = []): self
    {
        $this->prepend = $raw . ' ';
        array_push($this->params, ...$bindings);

        return $this;
    }

    /**
     * @return string
     * @throws SqlErrorException
     */
    public function buildQuery(): string
    {
        if($this->table) {
            if ($this->insert) {
                return $this->prepend . 'INSERT INTO ' . $this->table . ' ' . $this->insert . $this->append;
            }

            if ($this->update) {

                if (empty($this->where) && $this->force == false) {
                    throw new SqlErrorException("Where deyimi kullanmadan update işlemi yapmak için force true ayarlayın");
                }

                return $this->prepend . 'UPDATE ' . $this->table . $this->join . ' SET ' . $this->update . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
            }

            if ($this->delete) {

                if (empty($this->where) && $this->force == false) {
                    throw new SqlErrorException("Where deyimi kullanmadan delete işlemi yapmak için force true ayarlayın");
                }

                return $this->prepend . 'DELETE FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
            }

            $this->select = $this->select ?: '*';
            return $this->prepend . 'SELECT ' . $this->select . ' FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
        }

        return $this->prepend . $this->raw . $this->append;
    }

    /**
     * Hazırlanan sorguyu string ve çalıştırmaya hazır olarak döndürür
     * @return string
     */
    public function getQuery():string
    {
        try {
            $query = $this->buildQuery();
            $params = $this->bindingParams();
            $params = array_map(function ($v) {
                if (is_null($v)) {
                    return 'NULL';
                } elseif (is_int($v)) {
                    return $this->database()->pdo()->quote($v, PDO::PARAM_INT);
                }
                return $this->database()->pdo()->quote($v);
            }, $params);

            $string_params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);
            $numeric_params = array_filter($params, 'is_int', ARRAY_FILTER_USE_KEY);

            $query = str_replace(array_keys($string_params), array_values($string_params), $query);
            return preg_replace(array_fill(0, count($numeric_params), '/\?/'), $numeric_params, $query, 1);

        } catch (SqlErrorException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Sorguya dışarıdan dahil edilen değişkenlerin listesi
     * @return array
     */
    public function bindingParams(): array
    {
        return $this->params;
    }

    /**
     * Sorguyu getQuery yöntemini kullanarak çıktılar
     * Insert, update ve delete gibi sorgularda kullanışlıdır.
     * @return $this
     */
    public function debug(): self
    {
        $this->debug = true;
        return $this;
    }


    /**
     * @return string
     */
    protected function newParamName(): string
    {
        return ':param_' . ++$this->paramCount;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @param bool $isNull
     * @return string
     */
    protected function comparison(string $column, string $operant, $param, bool $isNull = true): string
    {
        if ($rawParam = $this->paramToRaw($param)) {
            $query = $this->quoteColumn($column) . ' ' . $operant . ' ' . $rawParam;
        } else {
            if ($param === null && $isNull === true) {
                if ($operant == '=') {
                    $query = $this->quoteColumn($column) . ' ' . 'IS NULL';
                } else {
                    $query = $this->quoteColumn($column) . ' ' . 'IS NOT NULL';
                }
            } else {
                $paramName = $this->newParamName();
                $query = $this->quoteColumn($column) . ' ' . $operant . ' ' . $paramName;
                $this->params[$paramName] = $param;
            }
        }

        return $query;
    }

    /**
     * @param string $column
     * @return string
     */
    protected function quoteColumn(string $column): string
    {
        if (preg_match("#^[\w]+$#", $column)) {
            return '`' . $column . '`';
        }

        return $column;
    }


    /**
     * @param $param
     * @return mixed|null
     */
    protected function paramToRaw($param)
    {
        if (preg_match("/^{{(.+)}}$/", $param, $matches)) {
            return $matches[1];
        }

        return null;
    }


    /**
     * Dışa kapalı sınıf değişkenlerine erişim sağlar, sorguda group, having veya join gibi
     * kompleks yapıların kullanılıp kullanılmadığını denetlemek için kullanılabilir.
     * @param $clauseName
     * params, table, select, insert, update, delete, force, where
     * group, having, order, limit, join, pk, paramCount, append, prepend, debug
     * @return mixed
     */
    public function getClause($clauseName)
    {
        if(property_exists($this, $clauseName)){
            return $this->$clauseName ?: false;
        }

        return false;
    }


    /*****************************************************************************************
     * DATABASE QUERY RUNNING
     *****************************************************************************************/

    /**
     * Sql insert deyimini çalıştırır
     * @param array $columns
     * Dizi anahtarları stun, değerleri ise insert edilecek veriyi temsil eden bir dizi alır
     * @return string|bool
     * @throws SqlErrorException
     */
    public function insert(array $columns)
    {
        $keys = array_keys($columns);
        $values = array_values($columns);
        $keys = array_map([$this, 'quoteColumn'], $keys);
        $tableCol = '(' . implode(',', $keys) . ')';
        $tableVal = 'VALUES(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $this->params = $values;
        $this->insert = $tableCol . ' ' . $tableVal;

        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->insert($this->buildQuery(), $this->bindingParams());
    }


    /**
     * Çoklu insert işlemleri için döngü ile insert işlemi yapmak yerine birleşik tek bir sorgu çalıştırma
     * imkanı sağlar. Her bir dizi bir insert işlemine denk gelecek biçimde iki boyutlu dizi alır.
     * @param array[][] $columns iki boyutlu dizi
     * @param int $fraction Tek bir sorguda en fazla kaç insert işleminin gerçekleşeceğini belirler.
     * @return int|string Başarı durumunda insert edilen satır sayısını döndürür
     * @throws SqlErrorException
     */
    public function multiLineInsert(array $columns, int $fraction = 1000)
    {
        $tableValAray = [];
        $lineCount = 0;
        $breaking = false;

        foreach ($columns as $column) {
            if (is_array($column)) {
                $tableValAray[] = '(' . implode(',', array_fill(0, count($column), '?')) . ') ';
                $this->params = array_merge($this->params, array_values($column));
            } else {
                throw new InvalidArgumentException("Çoklu insert için iki boyutlu bir dizi aktarılması gerekir.");
            }

            if (++$lineCount == $fraction) {
                $breaking = true;
                break;
            }
        }

        $keys = array_keys($columns[0]);
        $keys = array_map([$this, 'quoteColumn'], $keys);
        $tableCol = '(' . implode(',', $keys) . ')';
        $tableVal = 'VALUES ' . implode(',', $tableValAray);
        $this->insert = $tableCol . ' ' . $tableVal;

        if ($this->debug) {
            return $this->getQuery();
        }

        $this->database->insert($this->buildQuery(), $this->bindingParams());

        if ($breaking && count($columns) > $fraction) {
            $this->params = [];
            $this->multiLineInsert(array_slice($columns, $fraction), $fraction);
        }

        return count($columns);
    }

    /**
     * Sql update işlemi
     * @param string|array $column update edilecek stunları ve değerlerini içeren bir dizi
     * veya update edilecek tek bir stun adı
     * @param false|string $param dizi ile işlem yapılacak ise false tek bir stun ise stunun değeri
     * @param bool $force where koşulu kullanılmadan tablodaki tüm verilerin update edilmesi için true ayarlanmalı
     * @return bool|int|string
     * @throws SqlErrorException
     */
    public function update($column, $param = false, bool $force = false)
    {
        $this->force = $force;

        $query = [];

        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $query[] = $this-> comparison($key, '=', $value, false);
            }
        } elseif ($param !== false) {
            $query[] = $this->comparison($column, '=', $param, false);
        } else {
            throw new SqlErrorException("İlk parametre dizi olmalı veya ikinci parametre false dışında bir değer almalıdır.");
        }

        $this->update .= $this->update ? ', ' . implode(", ", $query) : implode(", ", $query);

        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->update($this->buildQuery(), $this->bindingParams());
    }


    /**
     * İnsert veya update edilmek istenen stun ve değerlerini içeren bir dizi alır.
     * Eğer primary key boş ise insert dolu ise update denenir.
     * Querybuilder $pk ayarlı değilse kullanılamaz
     * @param array $columns
     * @return bool|int|string
     * @throws SqlErrorException
     */
    public function upsert(array $columns)
    {
        if (!$this->pk) {
            throw new SqlErrorException('Querybuilder $pk değeri tanımlanmadı.');
        }

        try {
            if (empty($columns[$this->pk])) {
                return $this->insert($columns);
            }
        } catch (SqlErrorException $e) {
            unset($columns[$this->pk]);
            return $this->where($this->pk, $columns[$this->pk])->update($columns);
        }

        unset($columns[$this->pk]);
        return $this->where($this->pk, $columns[$this->pk])->update($columns);
    }

    /**
     * Sql delete işlemi
     * @param mixed $columns
     * Tek parametre tanımlanırsa ön tanımlı primary key ile silme işlemi gerçekleşir.
     * İlk parametre array atanırsa ve ikinci parametre false ise tüm dizi where koşulu için aranır
     * İlk parametr ve ikinci parametre string atanırsa tek bir where koşulu olarak kullanılır.
     * @param array|int|string $param
     * @param bool $force Silme işleminde where koşulu bulunmadan tüm tablo silinmek istenirse true ayarlanmalıdır.
     * @return bool|int
     * @throws SqlErrorException
     */
    public function delete($columns = null, $param = false, bool $force = false)
    {
        $this->delete = true;
        $this->force = $force;

        if (is_array($columns)) {
            $this->where($columns);
        } elseif ($this->pk && $columns !== null && $param === false) {
            $this->where($this->pk, $columns);
        } elseif (is_string($columns) && $param !== false) {
            $this->where($columns, $param);
        }

        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->delete($this->buildQuery(), $this->bindingParams());
    }


    /**
     * Soft delete işlemi için kullanılır.
     * Tabloda deleted_at stunu varsa ve değer null ise değer tarih ile değiştirilir.
     * @param array|int $columns
     * @return bool|int
     * @throws SqlErrorException
     */
    public function softDelete($columns = null, $param = false)
    {
        if (is_array($columns)) {
            return $this->where($columns)->update(['deleted_at' => '{{Now()}}']);
        } elseif ($this->pk && $columns !== null && $param === false) {
            return $this->where($this->pk, $columns)->update(['deleted_at' => '{{Now()}}']);
        } elseif (is_string($columns) && $param !== false) {
            $this->where($columns, $param);
        }

        return $this->update(['deleted_at' => '{{Now()}}']);
    }


    /**
     * Sql select değimini eşleşen tüm satırlar için gerçekleştirir
     * @param int $fetchStyle
     * @return array|string|string[]
     * @throws SqlErrorException
     */
    public function get(int $fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->get($this->buildQuery(), $this->bindingParams(), $fetchStyle);
    }

    /**
     * Sql select değimini eşleşen ilk satır için gerçekleştirir
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function getRow(int $fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->getRow($this->buildQuery(), $this->bindingParams(), $fetchStyle);
    }

    /**
     * Sql select değimini eşleşen ilk satırın ilk stunu için gerçekleştirir
     * @return mixed
     * @throws SqlErrorException
     */
    public function getVar()
    {
        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->getVar($this->buildQuery(), $this->bindingParams());
    }

    /**
     * Sql select değimini eşleşen tüm stunlar için gerçekleştirir
     * @return mixed
     * @throws SqlErrorException
     */
    public function getCol()
    {
        if ($this->debug) {
            return $this->getQuery();
        }

        return $this->database->getCol($this->buildQuery(), $this->bindingParams());
    }

    /**
     * Tanımlı primary key değeri ile where koşulu denenir.
     * pk tanımlı ise kullanılabilir
     * @param $param
     * @return mixed|array|bool
     * @throws Exception
     */
    public function find($param)
    {
        if (!$this->pk) {
            throw new Exception(__FUNCTION__ . " methodu pk değeri atanmadan kullanılamaz.");
        }

        return $this->where($this->pk, $param)->getRow();
    }

    /**
     * Tablonun sonundan belirtilen sayı kadar satır çeker, sıralama primary key ile yapılır
     * pk tanımlı ise kullanılabilir
     * @param int $rowCount
     * @return mixed
     * @throws Exception
     */
    public function last(int $rowCount = 1)
    {
        if (!$this->pk) {
            throw new Exception(__FUNCTION__ . " methodu pk değeri atanmadan kullanılamaz.");
        }

        if ($rowCount == 1) {
            return $this->order($this->pk, 'DESC')->limit($rowCount)->getRow();
        }

        return $this->order($this->pk, 'DESC')->limit($rowCount)->get();
    }

    /**
     * Tablonun başından belirtilen sayı kadar satır çeker, sıralama primary key ile yapılır
     * pk tanımlı ise kullanılabilir
     * @param int $rowCount
     * @return mixed
     * @throws Exception
     */
    public function first(int $rowCount = 1)
    {
        if (!$this->pk) {
            throw new Exception(__FUNCTION__ . " methodu pk değeri atanmadan kullanılamaz.");
        }

        if ($rowCount == 1) {
            return $this->order($this->pk)->limit($rowCount)->getRow();
        }

        return $this->order($this->pk)->limit($rowCount)->get();
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        return $this->database;
    }


    /**
     * Closure içinde transection başlatır ve kapatır.
     * İşlem başarılı ise sonuç döner, değilse exception fırlatır.
     * Birden fazla derinlikte transection başlatmaya izin verir.
     * @param Closure $callback
     * transection(function(){
     *      return query;
     * }
     * @return bool
     * @throws Exception
     */
    public function transaction(Closure $callback): bool
    {
        return $this->database->transaction($callback);
    }

    /**
     * Begin transection
     */
    public function beginTransaction(): void
    {
        $this->database->beginTransaction();
    }

    /**
     * Transection end with rollback
     */
    public function rollBack(): void
    {
        $this->database->rollBack();
    }

    /**
     * Transection end with commit
     */
    public function commit(): void
    {
        $this->database->commit();
    }
}
