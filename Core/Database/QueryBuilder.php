<?php 
/**
 * @Created 19.10.2020 20:00:33
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Eloquent
 * @package Core\Database
 */

namespace Core\Database;

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

    private string $queryType = '';
    private int $paramCount = 0;

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

            $paramName = $this->newParamName();
            $this->params[$paramName] = $value;
            $query[] = $key.' = '.$paramName;
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

            $paramName = $this->newParamName();
            $this->params[$paramName] = $value;
            $query[] = $key.' = '.$paramName;
        }

        $this->update .= $this->update ? ', '.implode(", ", $query) : implode(", ",$query);

        return $this;
    }


    /**
     * @return $this
     */
    public function delete()
    {
        $this->delete = true;

        return $this;
    }

    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function where(string $column, string $operant, $param)
    {
        $query = $this->andOrStatement($column, $operant, $param);

        $this->where .= $this->where ? ' AND '.$query : ' WHERE '.$query;

        return $this;
    }


    /**
     * @param string $column
     * @param string $operant
     * @param $param
     * @return $this
     */
    public function orWhere(string $column, string $operant, $param)
    {
        $query = $this->andOrStatement($column, $operant, $param);

        $this->where .= $this->where ? ' OR '.$query : ' WHERE '.$query;

        return $this;
    }


    /**
     * @param string $column
     * @param string $andOR
     */
    public function isNull(string $column, $andOR = 'AND')
    {
        $this->where .= $this->where ? ' '.$andOR.' '.$column.' IS NULL ' : ' WHERE '.$column.' IS NULL ';
    }


    /**
     * @param string $column
     * @param string $andOR
     */
    public function isNOTNull(string $column, $andOR = 'AND')
    {
        $this->where .= $this->where ? ' '.$andOR.' '.$column.' IS NOT NULL ' : ' WHERE '.$column.' IS NOT NULL ';
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
        $query = $this->andOrStatement($column, $operant, $param);

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
        $query = $this->andOrStatement($column, $operant, $param);

        $this->having .= $this->having ? ' OR '.$query : ' WHERE '.$query;

        return $this;
    }


    /**
     * @param callable $callback
     * @return string
     */
    public function subQuery(callable $callback)
    {
        $queryBuilder = new QueryBuilder();
        $callback($queryBuilder);
        $query = '{('.$queryBuilder->buildQuery().')}';

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
     * @return string
     */
    public function buildQuery()
    {
        if($this->select && $this->table != ''){

            $query = 'SELECT '.$this->select.' FROM '.$this->table.$this->join.$this->where.$this->group.$this->having.$this->order.$this->limit;
            $this->queryType = 'select';

        }elseif ($this->update && $this->table != ''){

            $query = 'UPDATE '.$this->table.$this->join.' SET '.$this->update.$this->where.$this->group.$this->having.$this->order.$this->limit;
            $this->queryType = 'update';

        }elseif ($this->delete && $this->table != ''){

            $query = 'DELETE FROM '.$this->table.$this->join.$this->where.$this->group.$this->having.$this->order.$this->limit;
            $this->queryType = 'delete';

        }elseif ($this->insert && $this->table != ''){

            $query = 'INSERT INTO '.$this->table.' SET '.$this->insert.$this->where.$this->group.$this->having.$this->order.$this->limit;
            $this->queryType = 'insert';
        }else{
            $query = '';
        }

        return $query;
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
    private function andOrStatement(string $column, string $operant, $param)
    {
        if(preg_match("/^\{(.+)\}$/", $param, $matches)){
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
     */
    public function __toString()
    {
        return $this->buildQuery();
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [$this->buildQuery(), $this->bindingParams()];
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
        if($query = $this->buildQuery()){
            return DB::get($query, $this->bindingParams(), $fetchStyle);
        }

        return [];
    }

    /**
     * @param int $fetchStyle
     * @return array|bool|mixed
     */
    public function getRow($fetchStyle = PDO::FETCH_OBJ)
    {
        if($query = $this->buildQuery()){
            return DB::getRow($query, $this->bindingParams(), $fetchStyle);
        }

        return [];
    }

    /**
     * @return mixed|bool
     */
    public function getVar()
    {
        if($query = $this->buildQuery()){
            return DB::getVar($query, $this->bindingParams());
        }

        return null;
    }

    /**
     * @param int $fetchStyle
     * @return array|bool
     */
    public function getCol($fetchStyle = PDO::FETCH_OBJ)
    {
        if($query = $this->buildQuery()){
            return DB::getCol($query, $this->bindingParams(), $fetchStyle);
        }

        return [];
    }


    /**
     * @return bool|int|string
     */
    public function run()
    {
        if($this->getQueryType() == 'insert'){
            return DB::insert($this->buildQuery(), $this->bindingParams());
        }elseif ($this->getQueryType() == 'update'){
            return DB::update($this->buildQuery(), $this->bindingParams());
        }elseif ($this->getQueryType() == 'delete'){
            return DB::delete($this->buildQuery(), $this->bindingParams());
        }else{
            return false;
        }
    }
}
