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
use Exceptions;
use InvalidArgumentException;
use PDO;

class QueryBuilder {

    protected array $params = [];
    protected string $table = '';
    protected string $select = '';
    protected string $insert = '';
    protected string $update = '';
    protected bool $delete = false;
    protected string $where = '';
    protected string $group = '';
    protected string $having = '';
    protected string $order = '';
    protected string $limit = '';
    protected string $join = '';
    protected string $pk = '';

    private string $queryType = '';
    private int $paramCount = 0;


    public function __construct()
    {
        Database::$backtrace = 4;
    }

    /**
     * @param string $primaryColumn
     * @return $this
     */
    public function pk(string $primaryColumn)
    {
        $this->pk = $primaryColumn;
        return $this;
    }

    /**
     * @param string $table
     * @param false $overwrite
     * @return $this
     */
    public function table(string $table, $overwrite = false)
    {
        if($overwrite){
            $this->table = $table;
        }else {
            $this->table .= $this->table ? ', ' . $table : $table;
        }

        return $this;
    }

    /**
     * @param string $select
     * @param false $overwrite
     * @return $this
     */
    public function select(string $select = "*", $overwrite = false)
    {
        if($overwrite) {
            $this->select = $select;
        }else{
            $this->select .= $this->select ? ', ' . $select : $select;
        }

        return $this;
    }


    /**
     * @param array $columns
     * @return $this
     */
    public function insert(array $columns)
    {
        $query = [];

        foreach ($columns as $key => $value){

            $query[] = $this->comparison($key, '=', $value);
        }

        $this->insert .= $this->insert ? ', '.implode(", ", $query) : implode(", ",$query);

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function update(array $columns)
    {
        $query = [];

        foreach ($columns as $key => $value){

            $query[] = $this->comparison($key, '=', $value);
        }

        $this->update .= $this->update ? ', '.implode(", ", $query) : implode(", ",$query);

        return $this;
    }


    /**
     * @param null $columns
     * @param null $param
     * @return $this
     * @throws Exception
     */
    public function delete($columns = null, $param = null)
    {
        $this->delete = true;

        if($columns !== null && $param !== null) {
            $this->where($columns, $param);
        }elseif (is_array($columns) && $param === null){
            $this->where($columns);
        }elseif(is_integer($columns)){

            if(!$this->pk){
                throw new Exception(__FUNCTION__." method cannot be used because primary key is not specified.");
            }else{
                $this->where($this->pk, (int) $columns);
            }
        }

        return $this;
    }


    /**
     * @param null $columns
     * @param null $param
     * @return $this
     */
    public function softDelete($columns = null, $param = null)
    {
        if($param !== null) {
            $this->update(['deleted_at' => '{{Now()}}'])->where($columns, $param);
        }elseif (is_array($columns)){
            $this->update(['deleted_at' => '{{Now()}}'])->where($columns);
        }elseif (is_integer($columns) && $this->pk){
            $this->update(['deleted_at' => '{{Now()}}'])->where($this->pk, $columns);
        }else{
            $this->update(['deleted_at' => '{{Now()}}']);
        }

        return $this;
    }


    /**
     * @param $column
     * @param $operant
     * @param $param
     * @return $this
     */
    public function where($column, $operant = null, $param = null)
    {
        if($param !== null && $operant !== null) {

            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
        }elseif($operant !== null && is_string($column)){

            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
        }else{
            if(is_array($column)){

                foreach ($column as $key => $val){

                    $query = $this->comparison($key, '=', $val);
                    $this->where .= $this->where ? ' AND ' . $query : ' WHERE ' . $query;
                }
            }else{
                throw new InvalidArgumentException("WHERE condition first parameter is must be an array or column name.");
            }
        }

        return $this;
    }


    /**
     * @param $column
     * @param null $operant
     * @param null $param
     * @return $this
     */
    public function orWhere($column, $operant = null, $param = null)
    {
        if($param !== null && $operant !== null) {

            $query = $this->comparison($column, $operant, $param);
            $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
        }elseif($operant !== null && is_string($column)){

            $query = $this->comparison($column, '=', $operant);
            $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
        }else{
            if(is_array($column)){

                foreach ($column as $key => $val){

                    $query = $this->comparison($key, '=', $val);
                    $this->where .= $this->where ? ' OR ' . $query : ' WHERE ' . $query;
                }
            }else{
                throw new InvalidArgumentException("WHERE condition first parameter is must be an array or column name.");
            }
        }

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     */
    public function isNull(string $column, $andOR = 'AND')
    {
        $this->where .= $this->where ? ' '.$andOR.' '.$column.' IS NULL ' : ' WHERE '.$column.' IS NULL ';

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     */
    public function isNOTNull(string $column, $andOR = 'AND')
    {
        $this->where .= $this->where ? ' '.$andOR.' '.$column.' IS NOT NULL ' : ' WHERE '.$column.' IS NOT NULL ';

        return $this;
    }

    /**
     * @param int $length
     * @param int $start
     * @return $this
     */
    public function limit(int $length, int $start = 0)
    {
        $this->limit = $start ?
            ' LIMIT '. $start .','.$length :
            ' LIMIT '.$length;

        return $this;
    }


    /**
     * @param string $column
     * @return $this
     */
    public function group(string $column)
    {
        $this->group .= $this->group ? ', '.$column :' GROUP BY '.$column;

        return $this;
    }


    /**
     * @param string $column
     * @param string $type
     * @return $this
     */
    public function order(string $column, string $type = "ASC")
    {
        $type = strtoupper($type);

        if(!in_array($type, ['ASC', 'DESC'])){
            $type = "ASC";
        }

        $this->order .= $this->order ? ', '.$column.' '.$type :' ORDER BY '.$column.' '.$type;

        return $this;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function having(string $column, string $operant, $param)
    {
        $query = $this->comparison($column, $operant, $param);

        $this->having .= $this->having ? ' AND '.$query : ' WHERE '.$query;

        return $this;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function orHaving(string $column, string $operant, $param)
    {
        $query = $this->comparison($column, $operant, $param);

        $this->having .= $this->having ? ' OR '.$query : ' WHERE '.$query;

        return $this;
    }


    /**
     * @param callable $callback
     * @return string
     * @throws Exception
     */
    public function subQuery(callable $callback)
    {
        $queryBuilder = new QueryBuilder();
        $callback($queryBuilder);
        $query = '{{('.$queryBuilder->buildQuery().')}}';

        foreach ($queryBuilder->bindingParams() as $param){
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
    public function join(string $table, string $matching)
    {
        $this->join .= ' JOIN '. $table . ' ON '. $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching
     * @return $this
     */
    public function leftJoin(string $table, string $matching)
    {
        $this->join .= ' LEFT JOIN '. $table . ' ON '. $matching;

        return $this;
    }

    /**
     * @param string $table
     * @param string $matching
     * @return $this
     */
    public function rightJoin(string $table, string $matching)
    {
        $this->join .= ' RIGHT JOIN '. $table . ' ON '. $matching;

        return $this;
    }


    /**
     * @param $condition
     * @param $callback
     * @return $this
     */
    public function cover($condition, $callback)
    {
        if(strcasecmp($condition, 'AND') === 0 || strcasecmp($condition, 'OR') === 0 || strcasecmp($condition, 'WHERE') === 0) {
            $this->where .= $this->where ? ' ' . $condition . ' (' : ' WHERE (';
            call_user_func($callback, $this);
            $this->where = str_replace('( AND ', '(', $this->where);
            $this->where = str_replace('( OR ', '(', $this->where);
            $this->where .= ')';
        }
        
        return $this;
    }

    /**
     * pk tanımlı ise kullanılabilir
     * @param $param
     * @return mixed|array|bool
     * @throws Exception
     */
    public function find($param)
    {
        if(!$this->pk){
            throw new Exception(__FUNCTION__." method cannot be used because primary key is not specified.");
        }

        return $this->select()->where($this->pk, $param)->getRow();
    }

    /**
     * pk tanımlı ise kullanılabilir
     * @param int $rowCount
     * @return array|bool
     * @throws Exception
     */
    public function last(int $rowCount = 1)
    {
        if(!$this->pk){
            throw new Exception(__FUNCTION__." method cannot be used because primary key is not specified.");
        }

        if($rowCount == 1) {
            return $this->select()->order($this->pk, 'DESC')->limit($rowCount)->getRow();
        }

        return $this->select()->order($this->pk, 'DESC')->limit($rowCount)->get();
    }

    /**
     * pk tanımlı ise kullanılabilir
     * @param int $rowCount
     * @return array|bool
     * @throws Exception
     */
    public function first(int $rowCount = 1)
    {
        if(!$this->pk){
            throw new Exception(__FUNCTION__." method cannot be used because primary key is not specified.");
        }

        if($rowCount == 1) {
            return $this->select()->order($this->pk)->limit($rowCount)->getRow();
        }

        return $this->select()->order($this->pk)->limit($rowCount)->get();
    }


    /**
     * @return string
     * @throws Exception
     */
    public function buildQuery()
    {
        if($this->table == ''){
            throw new Exception("No database table selected.");
        }

        if($this->select){

            $this->queryType = 'select';
            return 'SELECT '.$this->select.' FROM '.$this->table.$this->join.$this->where.$this->group.$this->having.$this->order.$this->limit;

        }elseif ($this->update){

            $this->queryType = 'update';
            return 'UPDATE '.$this->table.$this->join.' SET '.$this->update.$this->where.$this->group.$this->having.$this->order.$this->limit;

        }elseif ($this->delete){

            $this->queryType = 'delete';
            return 'DELETE FROM '.$this->table.$this->join.$this->where.$this->group.$this->having.$this->order.$this->limit;

        }elseif ($this->insert){

            $this->queryType = 'insert';
            return 'INSERT INTO '.$this->table.' SET '.$this->insert.$this->where.$this->group.$this->having.$this->order.$this->limit;
        }else{
            throw new Exception("Please choose one (select, insert, update or delete).");
        }
    }

    /**
     * @return array
     */
    public function bindingParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

    /**
     * @return string
     */
    private function newParamName()
    {
        return ':param_'.++$this->paramCount;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return string
     */
    private function comparison(string $column, string $operant, $param)
    {
        if(preg_match("/^\{\{(.+)\}\}$/", $param, $matches)){
            $query = $column. ' '.$operant.' '.$matches[1];
        }else{

            $paramName = $this->newParamName();
            $query = $column. ' '.$operant.' '.$paramName;
            $this->params[$paramName] = $param;
        }

        return $query;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        return $this->buildQuery();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function __debugInfo()
    {
        try {
            return [$this->buildQuery(), $this->bindingParams()];
        }catch (Exception $e){
            return [$e->getMessage()];
        }
    }

    /*****************************************************************************************
     * DATABASE QUERY RUNNING
     *****************************************************************************************/

    /**
     * @param int $fetchStyle
     * @return array|bool
     */
    public function get($fetchStyle = PDO::FETCH_OBJ)
    {
        try {
            if ($query = $this->buildQuery()) {
                return DB::get($query, $this->bindingParams(), $fetchStyle);
            }
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return [];
    }

    /**
     * @param int $fetchStyle
     * @return array|bool|mixed
     */
    public function getRow($fetchStyle = PDO::FETCH_OBJ)
    {
        try {
            if ($query = $this->buildQuery()) {
                return DB::getRow($query, $this->bindingParams(), $fetchStyle);
            }
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return [];
    }

    /**
     * @return bool|mixed|null
     */
    public function getVar()
    {
        try {
            if ($query = $this->buildQuery()) {
                return DB::getVar($query, $this->bindingParams());
            }
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return null;
    }

    /**
     * @param int $fetchStyle
     * @return array|bool
     */
    public function getCol($fetchStyle = PDO::FETCH_OBJ)
    {
        try {
            if ($query = $this->buildQuery()) {
                return DB::getCol($query, $this->bindingParams(), $fetchStyle);
            }
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return [];
    }


    /**
     * @param false $force
     * @return array|bool|int|string
     */
    public function run($force = false)
    {
        try {
            $query = $this->buildQuery();

            if ($this->getQueryType() == 'insert') {
                return DB::insert($query, $this->bindingParams());

            } elseif ($this->getQueryType() == 'update') {

                if ($this->where == '' && $force == false) {
                    throw new Exception("Use force true to delete without using where");
                } else {
                    return DB::update($query, $this->bindingParams());
                }

            } elseif ($this->getQueryType() == 'delete') {

                if ($this->where == '' && $force == false) {
                    throw new Exception("Use force true to delete without using where");
                } else {
                    return DB::delete($query, $this->bindingParams());
                }
            } else {
                return [$query, $this->bindingParams()];
            }
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return false;
    }
}
