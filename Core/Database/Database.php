<?php 
/**
 * @Created 18.05.2020 08:09:39
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Database
 * @package Core\Database
 */


namespace Core\Database;

use Core\App;
use PDO;
use PDOException;
use PDOStatement;

class Database {

    public App $app;
    private PDO $pdo;
    private PDOStatement $stm;
    private ConnectionInterface $connection;
    public array $config;


    /**
     * Database constructor.
     * @param ConnectionInterface $connection
     * @param App $app
     * @throws SqlErrorException
     */
    public function __construct(ConnectionInterface $connection, App $app)
    {
        $this->connection = $connection;
        $this->app = $app;

        try {
            $this->pdo = $connection->connection();
            $this->config = $this->connection->config;
        } catch (PDOException $e) {
            throw new SqlErrorException($this->connection->config['driver']." veritabanı bağlantısı kurulamıyor.", E_ERROR, $e);
        }
    }

    /**
     * Yeniden database seçer
     * @param $database
     * @return $this
     * @throws SqlErrorException
     */
    public function selectDB(string $database):Database
    {
        if($this->pdo->exec("USE $database") === false){
            throw new SqlErrorException("Veritabanı seçilemedi.", E_ERROR);
        }

        return $this;
    }


    /**
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table = ''):QueryBuilder
    {
        $queryBuilder = __NAMESPACE__.'\\'.ucfirst($this->config['driver'])."QueryBuilder";
        /**
         * @var QueryBuilder $queryBuilderInstance
         */
        $queryBuilderInstance = new $queryBuilder($this);
        return $queryBuilderInstance->table($table);
    }


    /**
     * @param $query
     * @param array|null $bindings
     * @param array $options
     * @return PDOStatement
     * @throws SqlErrorException
     */
    public function bindQuery($query, array $bindings = null, array $options = []):PDOStatement
    {
        $this->stm = $this->pdo->prepare($query, $options);
        $this->stm->execute($bindings);
        $sqlError = $this->stm->errorInfo();

        if(isset($sqlError[1])){
            throw new SqlErrorException("Sql error code({$sqlError[0]}/{$sqlError[1]}) Error: {$sqlError[2]}", E_ERROR);
        }

        return $this->stm;
    }


    /**
     * Eşleşen tüm satırları döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function get(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
    {
        return $this->bindQuery($query, $bindings)->fetchAll($fetchStyle);
    }


    /**
     * Eşleşen ilk satırı döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return mixed
     * @throws SqlErrorException
     */
    public function getRow(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
    {
        return $this->bindQuery($query, $bindings)->fetch($fetchStyle);
    }


    /**
     * Sorgu sonucu dönen ilk stunun tamanını döndürür
     * @param string $query
     * @param array|null $bindings
     * @return mixed
     * @throws SqlErrorException
     */
    public function getCol(string $query, array $bindings = null)
    {
        return $this->bindQuery($query, $bindings)->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Eşleşen ilk satırdan belirtilen stunu döndürür
     * @param string $query
     * @param array|null $bindings
     * @return mixed
     * @throws SqlErrorException
     */
    public function getVar(string $query, array $bindings = null)
    {
        return $this->bindQuery($query, $bindings)->fetchColumn();
    }


    /**
     * İnsert edilen son satırın autoincrement değerini döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|string
     * @throws SqlErrorException
     */
    public function insert(string $query, array $bindings = null)
    {
        $this->bindQuery($query, $bindings);
        return $this->pdo->lastInsertId();
    }


    /**
     * Update işleminden etkilenen satır sayısını döndürür,
     * etkilenen satır yoksa true, hata oluşursa false döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     * @throws SqlErrorException
     */
    public function update(string $query, array $bindings = null)
    {
        $this->bindQuery($query, $bindings);
        return $this->stm->rowCount() ? $this->stm->rowCount() : true;
    }


    /**
     * Silme işleminden etkilenen satır sayısını döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     * @throws SqlErrorException
     */
    public function delete(string $query, array $bindings = null)
    {
        $this->bindQuery($query, $bindings);
        return $this->stm->rowCount();
    }

    /**
     * Aktif veritabanı bağlantısını sonlandırır
     */
    public function close()
    {
        unset($this->pdo, $this->stm);
    }

    /**
     * PDO::beginTransaction()
     */
    public function transaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * PDO::commit()
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * PDO::rollBack()
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * Aktif PDO nesnesi
     * @return PDO
     */
    public function pdo():PDO
    {
        return $this->pdo;
    }

    /**
     * Aktif PDOStatement nesnesi
     * @return PDOStatement
     */
    public function stm():PDOStatement
    {
        return $this->stm;
    }
}
