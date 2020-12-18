<?php 
/**
 * @Created 21.10.2020 20:29:04
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class DataTable
 * @package Helpers
 */


namespace Helpers;


use Core\Database\QueryBuilder;
use DB;
use PDO;

class DataTable {

    private array $request = [];
    private array $response = [
        'recordsTotal' => 0,
        'recordFiltered' => 0,
        'data' => []
    ];

    private QueryBuilder $query;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table):QueryBuilder
    {
        return $this->query = DB::table($table);
    }


    /**
     * Column key is javascript data column name (example), value is mysql query column name (t.example)
     * @param array $columns
     */
    public function orderable(array $columns)
    {
        $this->request['columns'] = $this->request['columns'] ?? [];

        foreach ($this->request['columns'] as $key => $column){

            if($column['orderable'] == 'true' && in_array($column['data'], array_keys($columns))){

                if(isset($this->request['order'][$key]['column'])){
                    $this->query->order($columns[$column['data']], $this->request['order'][$key]['dir']);
                }
            }
        }
    }

    /**
     * Column key is javascript data column name (example), value is mysql query column name (t.example)
     *
     * @param array $columns
     * @param int $minLength
     */
    public function searchable(array $columns, int $minLength = 3)
    {
        $this->request['columns'] = $this->request['columns'] ?? [];

        foreach ($this->request['columns'] as $key => $column){

            if($column['searchable'] == 'true' && in_array($column['data'], array_keys($columns))){

                if(!empty($column['search']['values']) && strlen($column['search']['values']) >= $minLength){
                    $this->query->orWhere($column, 'like' , '%'.$column['search']['values'].'%');
                }

                if(!empty($this->request['search']['value']) && strlen($this->request['search']['value']) >= $minLength){
                    $this->query->orWhere($columns[$column['data']], 'like' , '%'.$this->request['search']['value'].'%');
                }
            }
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function recordsTotalQuery():QueryBuilder
    {
        $recordsTotalQuery = clone $this->query;
        $recordsTotalQuery->select('Count(1)', true);

        return $recordsTotalQuery;
    }


    /**
     * @return array
     * @throws \Core\Database\SqlErrorException
     */
    public function result():array
    {
        $this->response['data'] = $this->query->limit(intval($this->request['start']), intval($this->request['length']))->get(PDO::FETCH_ASSOC);
        $this->response['recordsTotal'] = $this->recordsTotalQuery()->getVar();
        $this->response['recordFiltered'] = count($this->response['data']);

        return $this->response;
    }
}

/**
EXAMPLE

$dataTable = new DataTable(Request::get());

$dataTable->table('users u')
->select('w.withdrawID, u.nameSurname, u.userEmail, u.withdrawAddress, u.userID, w.status, w.created_at, w.updated_at')
->join('withdraw w', 'u.userID = w.userID')
->order("w.updated_at");

$dataTable->orderable([
'withdrawID' => 'w.withdrawID',
'nameSurname' => 'u.nameSurname',
'userEmail' => 'u.userEmail',
'status' => 'w.status',
'created_at' => 'w.created_at',
'updated_at' => 'w.updated_at',
]);
$dataTable->searchable([
'nameSurname' => 'u.nameSurname',
'userEmail' => 'u.userEmail',
'status' => 'w.status',
]);

dump($dataTable->result());
 */
