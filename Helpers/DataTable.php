<?php

namespace Helpers;


use Core\Database\QueryBuilder;
use Core\Database\SqlErrorException;
use DB;
use PDO;

class  DataTable
{
    /**
     * @var callable $callback
     */
    private $callback = null;
    private array $request;
    private array $response = [
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ];

    private QueryBuilder $query;
    private ?QueryBuilder $filteredQuery = null;
    private QueryBuilder $totalRecordQuery;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table): QueryBuilder
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

        if ($this->request['order']) {
            $this->query->order("");
        }

        foreach ($this->request['order'] as $order) {

            $orderable = $this->request['columns'][$order['column']]['orderable'];
            $columnData = $this->request['columns'][$order['column']]['data'];

            if ($orderable == 'true' && array_key_exists($columnData, $columns)) {

                $this->query->order($columns[$columnData], $order['dir']);
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

        $searchQuery->cover("and", function ($query) use ($columns, $minLength, &$search) {

            $searchableColumns = [];

            //belirli bir stunda arama
            foreach ($this->request['columns'] as $column) {

                if ($column['searchable'] == 'true' && array_key_exists($column['data'], $columns)) {

                    $searchableColumns[] = $column['data'];

                    //tek bir stunda arama
                    if (!empty($column['search']['value']) && strlen($column['search']['value']) >= $minLength) {

                        $searchValue = $column['search']['value'];

                        if (is_array($columns[$column['data']])) {
                            $table_column = array_shift($columns[$column['data']]);
                            $operator = array_shift($columns[$column['data']][1]) ?: 'LIKE';
                        } else {
                            $table_column = $columns[$column['data']];
                            $operator = 'LIKE';
                        }

                        if ($operator == 'LIKE') {
                            $searchValue = '%' . $column['search']['value'] . '%';
                        }

                        $query->where($table_column, $operator, $searchValue);
                        $search = true;
                    }
                }
            }

            //tüm stunlarda arama
            foreach ($columns as $key => $column) {

                if (!empty($this->request['search']['value']) && in_array($key, $searchableColumns) && strlen($this->request['search']['value']) >= $minLength) {

                    $searchValue = $this->request['search']['value'];

                    if (is_array($column)) {
                        $table_column = array_shift($column);
                        $operator = array_shift($column) ?: 'LIKE';
                    } else {
                        $table_column = $column;
                        $operator = 'LIKE';
                    }

                    if ($operator == 'LIKE') {
                        $searchValue = '%' . $this->request['search']['value'] . '%';
                    }

                    $query->orWhere($table_column, $operator, $searchValue);
                    $search = true;
                }
            }
        });

        //arama varsa ve sonuç sayısını değiştirmişse filtrelenmiş veri sayısını alır
        if ($search) {
            $this->query = clone $searchQuery;
            if ($searchQuery->getClause('having') || $searchQuery->getClause('group')) {
                $searchRawQuery = $searchQuery->select("Count(1)", true)->order("")->getQuery();
                $this->filteredQuery = DB::table()->raw("SELECT Count(1) FROM ($searchRawQuery) as temp_table");
            } else {
                $this->filteredQuery = $searchQuery->select("Count(1)", true)->order("");
            }
        }
    }


    /**
     * Toplam kayıt sayısı
     */
    protected function recordsTotalQuery()
    {
        $recordsTotalQuery = clone $this->query;

        if ($recordsTotalQuery->getClause('having') || $recordsTotalQuery->getClause('group')) {
            $rawQuery = $recordsTotalQuery->select("Count(1)", true)->order("")->getQuery();
            $this->totalRecordQuery = DB::table()->raw("SELECT Count(1) FROM ($rawQuery) as temp_table");
        } else {
            $this->totalRecordQuery = $recordsTotalQuery->select("Count(1)", true)->order("");
        }
    }


    /**
     * @param callable $callback = function(array $row)
     */
    public function callback(callable $callback)
    {
        $this->callback = $callback;
    }


    /**
     * @param $datas
     * @return array
     */
    protected function callCallback($datas): array
    {
        if ($this->callback) {
            foreach ($datas as $key => $data) {
                $datas[$key] = ($this->callback)($data);
            }
        }

        return $datas;
    }


    /**
     * @param int $fetchType
     * @return array
     * @throws SqlErrorException
     */
    public function result(int $fetchType = PDO::FETCH_OBJ): array
    {
        $this->recordsTotalQuery();

        $results = $this->query->limit($this->request['start'], $this->request['length'])->get($fetchType);
        $this->response['data'] = $this->callCallback($results);

        if ($this->filteredQuery) {
            $this->response['recordsFiltered'] = $this->filteredQuery->getVar();
        }

        $this->response['recordsTotal'] = (int)$this->totalRecordQuery->getVar();
        $this->response['recordsFiltered'] = $this->response['recordsFiltered'] ?: $this->response['recordsTotal'];

        return $this->response;
    }
}

/**
 * EXAMPLE
 *
 * $dataTable = new DataTable(Request::get(), 'name');
 *
 * $dataTable->table('users u')
 * ->select('w.withdrawID, u.nameSurname, u.userEmail, u.withdrawAddress, u.userID, w.status, w.created_at, w.updated_at')
 * ->join('withdraw w', 'u.userID = w.userID')
 * ->order("w.updated_at");
 *
 * $dataTable->orderable([
 * 'withdrawID' => 'w.withdrawID',
 * 'nameSurname' => 'u.nameSurname',
 * 'userEmail' => 'u.userEmail',
 * 'status' => 'w.status',
 * 'created_at' => 'w.created_at',
 * 'updated_at' => 'w.updated_at',
 * ]);
 * $dataTable->searchable([
 * 'nameSurname' => ['u.username', 'like'],
 * 'userEmail' => ['u.userEmail', '='],
 * 'status' => 'w.status',
 * ]);
 *
 * $dataTable->callback(function($data){
 * $data['nameSurname'] = strtoupper($data['nameSurname']);
 * return $data;
 * }
 *
 * dump($dataTable->result());
 */
