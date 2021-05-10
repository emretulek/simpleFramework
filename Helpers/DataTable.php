<?php 

namespace Helpers;


use Core\Database\QueryBuilder;
use DB;
use PDO;

class DataTable {

    private array $request = [];
    private array $response = [
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
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
        $this->request['order'] = $this->request['order'] ?? [];

        foreach ($this->request['order'] as $order){

            if(isset($order['column']) && array_key_exists($order['column'], $columns)){

                $this->query->order("")->order($columns[$order['column']], $order['dir']);
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

        $searchQuery = clone $this->query;
        $search = false;

        $searchQuery->cover("and", function ($query) use ($columns, $minLength, &$search){
            foreach ($this->request['columns'] as $column){

                if($column['searchable'] == 'true' && array_key_exists($column['data'], $columns)){

                    if(!empty($column['search']['value']) && strlen($column['search']['value']) >= $minLength){
                        $query->orWhere($column, 'like' , '%'.$column['search']['value'].'%');
                        $search = true;
                    }

                    if(!empty($this->request['search']['value']) && strlen($this->request['search']['value']) >= $minLength){
                        $query->orWhere($columns[$column['data']], 'like' , '%'.$this->request['search']['value'].'%');
                        $search = true;
                    }
                }
            }
        });

        //arama varsa ve sonuç sayısını değiştirmişse filtrelenmiş veri sayısını alır
        if($search){
            $this->query = clone $searchQuery;
            $this->response['recordsFiltered'] = $searchQuery->select("Count(1)", true)->order("")->getVar();
        }
    }

    /**
     * @return int
     * @throws \Core\Database\SqlErrorException
     */
    protected function recordsTotalQuery():int
    {
        $recordsTotalQuery = clone $this->query;
        return $recordsTotalQuery->select('Count(1)', true)->order("")->getVar();
    }


    /**
     * @return array
     * @throws \Core\Database\SqlErrorException
     */
    public function result():array
    {
        $this->response['recordsTotal'] = (int) $this->recordsTotalQuery();
        $this->response['data'] = $this->query->limit(intval($this->request['start']), intval($this->request['length']))->get(PDO::FETCH_NUM);
        $this->response['recordsFiltered'] = $this->response['recordsFiltered'] ? $this->response['recordsFiltered'] : $this->response['recordsTotal'];

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
