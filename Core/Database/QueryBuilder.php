<?php
/**
 * @Created 19.10.2020 20:00:33
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Eloquent
 * @package Core\Database
 */

namespace Core\Database;

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
    public function table(string $table, $overwrite = false): self
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
    public function select(string $select = "*", $overwrite = false): self
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
     * @param array $columns
     * @return array|bool|string
     * @throws SqlErrorException
     */
    public function insert(array $columns)
    {
        $query = [];

        foreach ($columns as $key => $value) {

            $query[] = $this->comparison($key, '=', $value);
        }

        $this->insert .= $this->insert ? ', ' . implode(", ", $query) : implode(", ", $query);

        if ($this->debug) {
            return [$this->buildQuery(), $this->bindingParams()];
        }

        return $this->database->insert($this->buildQuery(), $this->bindingParams());
    }

    /**
     * @param array $columns
     * @return array|bool|int
     * @throws SqlErrorException
     */
    public function update(array $columns, $force = false)
    {
        $this->force = $force;

        $query = [];

        foreach ($columns as $key => $value) {

            $query[] = $this->comparison($key, '=', $value);
        }

        $this->update .= $this->update ? ', ' . implode(", ", $query) : implode(", ", $query);

        if ($this->debug) {
            return [$this->buildQuery(), $this->bindingParams()];
        }

        return $this->database->update($this->buildQuery(), $this->bindingParams());
    }


    /**
     * @param array|int $columns
     * @param bool $force
     * @return array|bool|int
     * @throws SqlErrorException
     */
    public function delete($columns, bool $force = false)
    {
        $this->delete = true;
        $this->force = $force;

        if (is_array($columns)) {
            $this->where($columns);
        }elseif($this->pk){
            $this->where($this->pk, $columns);
        }else{
            throw new InvalidArgumentException("\$column [column_name => value] ilişkili bir dizi olmalı.");
        }

        if ($this->debug) {
            return [$this->buildQuery(), $this->bindingParams()];
        }

        return $this->database->delete($this->buildQuery(), $this->bindingParams());
    }


    /**
     * @param array|int $columns
     * @return bool|int
     * @throws SqlErrorException
     */
    public function softDelete($columns)
    {
        if (is_array($columns)) {
            return $this->where($columns)->update(['deleted_at' => '{{Now()}}']);
        }elseif($this->pk){
            return $this->where($this->pk, $columns)->update(['deleted_at' => '{{Now()}}']);
        }else{
            throw new InvalidArgumentException("\$column [column_name => value] ilişkili bir dizi olmalı.");
        }
    }


    /**
     * @param $column
     * @param $operant
     * @param $param
     * @return $this
     */
    public function where($column, $operant = null, $param = null): self
    {
        if (is_array($column)) {

            $operant = $operant ? $operant : '=';

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
            }

        } elseif (is_string($column) && is_null($param)) {
            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
        }

        return $this;
    }


    /**
     * @param $column
     * @param null $operant
     * @param null $param
     * @return $this
     */
    public function orWhere($column, $operant = null, $param = null): self
    {
        if (is_array($column)) {

            $operant = $operant ? $operant : '=';

            foreach ($column as $key => $val) {
                $query = $this->comparison($key, $operant, $val);
                $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
            }

        } elseif (is_string($column) && is_null($param)) {
            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
        } else {
            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
        }

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNull(string $column, $andOR = 'AND'): self
    {
        $this->where .= $this->where ? ' ' . $andOR . ' ' . $this->quoteColumn($column) . ' IS NULL ' : ' WHERE ' . $column . ' IS NULL ';

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     * @return $this
     */
    public function isNotNull(string $column, $andOR = 'AND'): self
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
            ' LIMIT ' . $start . ',' . $length :
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

        $this->order .= $this->order ?
            ', ' . $this->quoteColumn($column) . ' ' . $type :
            ' ORDER BY ' . $this->quoteColumn($column) . ' ' . $type;

        return $this;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function having(string $column, string $operant, $param): self
    {
        $query = $this->comparison($column, $operant, $param);

        $this->having .= $this->having ? ' AND ' . $query : ' WHERE ' . $query;

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
        $query = $this->comparison($column, $operant, $param);

        $this->having .= $this->having ? ' OR ' . $query : ' WHERE ' . $query;

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
     * @return string
     * @throws SqlErrorException
     */
    public function buildQuery(): string
    {
        if ($this->table == '') {
            throw new SqlErrorException("İşlem yapılacak veritabanı tablosu seçilmedi.");
        }

        if ($this->insert) {

            return 'INSERT INTO ' . $this->table . ' SET ' . $this->insert . $this->where . $this->group . $this->having . $this->order . $this->limit;
        } elseif ($this->update) {

            if (empty($this->where) && $this->force == false) {
                throw new SqlErrorException("Where deyimi kullanmadan update işlemi yapmak için force true ayarlayın");
            }

            return 'UPDATE ' . $this->table . $this->join . ' SET ' . $this->update . $this->where . $this->group . $this->having . $this->order . $this->limit;
        } elseif ($this->delete) {

            if (empty($this->where) && $this->force == false) {
                throw new SqlErrorException("Where deyimi kullanmadan delete işlemi yapmak için force true ayarlayın");
            }

            return 'DELETE FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit;
        } elseif ($this->select) {

            return 'SELECT ' . $this->select . ' FROM ' . $this->table . $this->join . $this->where . $this->group . $this->having . $this->order . $this->limit;
        } else {
            throw new SqlErrorException("[select, insert, update, delete] deyimlerinden en az birini kullanmalısınız.");
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
    public function debug()
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
     * @return string
     */
    protected function comparison(string $column, string $operant, $param): string
    {
        if (preg_match("/^\{\{(.+)\}\}$/", $param, $matches)) {
            $query = $this->quoteColumn($column) . ' ' . $operant . ' ' . $matches[1];
        } else {
            if($param === null){
                if($operant == '=') {
                    $query = $this->quoteColumn($column) . ' ' . 'IS NULL';
                }else{
                    $query = $this->quoteColumn($column) . ' ' . 'IS NOT NULL';
                }
            }else {
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
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function get($fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return [$this->buildQuery(), $this->bindingParams()];
        }

        return $this->database->get($this->buildQuery(), $this->bindingParams(), $fetchStyle);
    }

    /**
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function getRow($fetchStyle = PDO::FETCH_OBJ)
    {
        if ($this->debug) {
            return [$this->buildQuery(), $this->bindingParams()];
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
            return [$this->buildQuery(), $this->bindingParams()];
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
            return [$this->buildQuery(), $this->bindingParams()];
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
        if ($this->pk) {
            return $this->select()->where($this->pk, $param)->getRow();
        }

        throw new Exception(__FUNCTION__ . " methodu pk değeri atanmadan kullanılamaz.");
    }

    /**
     * pk tanımlı ise kullanılabilir
     * @param int $rowCount
     * @return mixed
     * @throws Exception
     */
    public function last(int $rowCount = 1)
    {
        if ($this->pk) {
            if ($rowCount == 1) {
                return $this->select()->order($this->pk, 'DESC')->limit($rowCount)->getRow();
            }

            return $this->select()->order($this->pk, 'DESC')->limit($rowCount)->get();
        }

        throw new Exception(__FUNCTION__ . " methodu pk değeri atanmadan kullanılamaz.");
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

        if ($rowCount == 1) {
            return $this->select()->order($this->pk)->limit($rowCount)->getRow();
        }

        return $this->select()->order($this->pk)->limit($rowCount)->get();
    }
}
