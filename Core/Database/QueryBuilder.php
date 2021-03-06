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

        if ($overwrite) {
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
     * @param string $connective
     * @return $this
     */
    public function where($column, $operant = '=', $param = false, string $connective = 'AND'): self
    {
        if (is_array($column)) {

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->where .= $this->where ? " $connective " . $query : ' WHERE ' . $query;
            }

        } elseif ($param === false) {
            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? " $connective " . $query : ' WHERE ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? " $connective " . $query : ' WHERE ' . $query;
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
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNull(string $column, string $andOR = 'AND'): self
    {
        $this->where .= $this->where ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IS NULL ' : ' WHERE ' . $column . ' IS NULL ';

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNotNull(string $column, string $andOR = 'AND'): self
    {
        $this->where .= $this->where ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IS NOT NULL ' : ' WHERE ' . $column . ' IS NOT NULL ';

        return $this;
    }

    /**
     * @param int $length
     * @param int $start
     * @return $this
     */
    public function limit(int $length, int $start = 0): self
    {
        $this->limit = $start ?
            ' LIMIT ' . $length . ',' . $start :
            ' LIMIT ' . $length;

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
            ' GROUP BY ' . $column;

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
     * @param $column
     * @param mixed $operant
     * @param mixed $param
     * @param string $connective
     * @return $this
     */
    public function having($column, $operant = '=', $param = false, string $connective = 'AND'): self
    {
        if (is_array($column)) {

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->having .= $this->having ? " $connective " . $query : ' WHERE ' . $query;
            }

        } elseif ($param === false) {
            $query = $this->comparison($column, '=', $operant);
            $this->having .= $this->having ? " $connective " . $query : ' WHERE ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->having .= $this->having ? " $connective " . $query : ' WHERE ' . $query;
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
     * @param callable $callback
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
     * @param string $matching
     * @return $this
     */
    public function join(string $table, string $matching): self
    {
        $this->join .= ' JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching
     * @return $this
     */
    public function leftJoin(string $table, string $matching): self
    {
        $this->join .= ' LEFT JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching
     * @return $this
     */
    public function rightJoin(string $table, string $matching): self
    {
        $this->join .= ' RIGHT JOIN ' . $this->quoteColumn($table) . ' ON ' . $matching;

        return $this;
    }


    /**
     * @param $condition
     * @param $callback
     * @return $this
     */
    public function cover($condition, $callback): self
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
     * sorgu sonuna ekleme
     * @param string $raw
     * @return $this
     */
    public function append(string $raw): self
    {
        $this->append = ' ' . $raw;

        return $this;
    }


    /**
     * sorgu sonuna ekleme
     * @param string $raw
     * @return $this
     */
    public function prepend(string $raw): self
    {
        $this->prepend = $raw . ' ';

        return $this;
    }

    /**
     * @return string
     * @throws SqlErrorException
     */
    public function buildQuery(): string
    {
        if ($this->table == '') {
            throw new SqlErrorException("İşlem yapılacak veritabanı tablosu seçilmedi.");
        }

        if ($this->insert) {

            return $this->prepend . 'INSERT INTO ' . $this->table . ' ' . $this->insert . $this->append;
        } elseif ($this->update) {

            if (empty($this->where) && $this->force == false) {
                throw new SqlErrorException("Where deyimi kullanmadan update işlemi yapmak için force true ayarlayın");
            }

            return $this->prepend . 'UPDATE ' . $this->table . $this->join . ' SET ' . $this->update . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
        } elseif ($this->delete) {

            if (empty($this->where) && $this->force == false) {
                throw new SqlErrorException("Where deyimi kullanmadan delete işlemi yapmak için force true ayarlayın");
            }

            return $this->prepend . 'DELETE FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
        } elseif ($this->select) {

            return $this->prepend . 'SELECT ' . $this->select . ' FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit . $this->append;
        } else {
            throw new SqlErrorException("[select, insert, update, delete] deyimlerinden en az birini kullanmalısınız.");
        }
    }

    /**
     * @return array|string|string[]
     */
    public function parseQuery()
    {
        try {
            $query = $this->buildQuery();
            $params = $this->bindingParams();
            $params = array_map(function ($v) {
                if (is_null($v)) {
                    return 'null';
                } elseif (is_int($v) || is_float($v)) {
                    return $v;
                }
                return "'$v'";
            }, $params);

            if (isset($params[0])) {
                return preg_replace(array_fill(0, count($params), '/\?/'), $params, $query, 1);
            }

            return str_replace(array_keys($params), array_values($params), $query);


        } catch (SqlErrorException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function bindingParams(): array
    {
        return $this->params;
    }

    /**
     * debug query
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
        if (preg_match("/^{{(.+)}}$/", $param, $matches)) {
            $query = $this->quoteColumn($column) . ' ' . $operant . ' ' . $matches[1];
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


    /*****************************************************************************************
     * DATABASE QUERY RUNNING
     *****************************************************************************************/

    /**
     * @param array $columns
     * @return string
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
            return $this->parseQuery();
        }

        return $this->database->insert($this->buildQuery(), $this->bindingParams());
    }


    /**
     * @param array $columns
     * @param int $fraction
     * @return array|int
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
            return $this->parseQuery();
        }

        $this->database->insert($this->buildQuery(), $this->bindingParams());

        if ($breaking && count($columns) > $fraction) {
            $this->params = [];
            $this->multiLineInsert(array_slice($columns, $fraction), $fraction);
        }

        return count($columns);
    }

    /**
     * @param $column
     * @param false|string $param
     * @param bool $force
     * @return array|bool|int|string|string[]
     * @throws SqlErrorException
     */
    public function update($column, $param = false, bool $force = false)
    {
        $this->force = $force;

        $query = [];

        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $query[] = $this->comparison($key, '=', $value, false);
            }
        } elseif ($param !== false) {
            $query[] = $this->comparison($column, '=', $param, false);
        } else {
            throw new SqlErrorException("İlk parametre dizi olmalı veya ikinci parametre false dışında bir değer almalıdır.");
        }

        $this->update .= $this->update ? ', ' . implode(", ", $query) : implode(", ", $query);

        if ($this->debug) {
            return $this->parseQuery();
        }

        return $this->database->update($this->buildQuery(), $this->bindingParams());
    }


    /**
     * Querybuilder $pk ayarlı değilse kullanılamaz
     * @param array $columns
     * @return array|bool|int|string|string[]
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
     * @param mixed $columns
     * @param mixed $param
     * @param bool $force
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
            return $this->parseQuery();
        }

        return $this->database->delete($this->buildQuery(), $this->bindingParams());
    }


    /**
     * @param array|int $columns
     * @return bool|int
     * @throws SqlErrorException
     */
    public function softDelete($columns = null, $param = false, bool $force = false)
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
     * @param int $fetchStyle
     * @return array|string|string[]
     * @throws SqlErrorException
     */
    public function get(int $fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return $this->parseQuery();
        }

        return $this->database->get($this->buildQuery(), $this->bindingParams(), $fetchStyle);
    }

    /**
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function getRow(int $fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return $this->parseQuery();
        }

        return $this->database->getRow($this->buildQuery(), $this->bindingParams(), $fetchStyle);
    }

    /**
     * @return mixed
     * @throws SqlErrorException
     */
    public function getVar()
    {
        if ($this->debug) {
            return $this->parseQuery();
        }

        return $this->database->getVar($this->buildQuery(), $this->bindingParams());
    }

    /**
     * @return mixed
     * @throws SqlErrorException
     */
    public function getCol()
    {
        if ($this->debug) {
            return $this->parseQuery();
        }

        return $this->database->getCol($this->buildQuery(), $this->bindingParams());
    }

    /**
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

        if(!$this->select){
            $this->select();
        }

        return $this->select()->where($this->pk, $param)->getRow();
    }

    /**
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

        if(!$this->select){
            $this->select();
        }

        if ($rowCount == 1) {
            return $this->order($this->pk, 'DESC')->limit($rowCount)->getRow();
        }

        return $this->order($this->pk, 'DESC')->limit($rowCount)->get();
    }

    /**
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

        if(!$this->select){
            $this->select();
        }

        if ($rowCount == 1) {
            return $this->select()->order($this->pk)->limit($rowCount)->getRow();
        }

        return $this->select()->order($this->pk)->limit($rowCount)->get();
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        return $this->database;
    }


    /**
     * @param Closure $callback
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
