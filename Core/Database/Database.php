<?php

namespace Core\Database;

use Closure;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;
    private PDOStatement $stm;
    private ConnectionInterface $connection;
    private int $transectionCount = 0;
    public array $config;


    /**
     * Database constructor.
     * @param ConnectionInterface $connection
     * @throws SqlErrorException
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        try {
            $this->pdo = $connection->connection();
            $this->config = $this->connection->config;
        } catch (PDOException $e) {
            throw new SqlErrorException($this->connection->config['driver'] . " veritabanı bağlantısı kurulamıyor.", E_ERROR, $e);
        }
    }

    /**
     * Yeniden database seçer
     * @param string $database
     * @return $this
     * @throws SqlErrorException
     */
    public function selectDB(string $database): Database
    {
        if ($this->pdo->exec("USE $database") === false) {
            throw new SqlErrorException("Veritabanı seçilemedi.", E_ERROR);
        }

        return $this;
    }


    /**
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table = ''): QueryBuilder
    {
        $queryBuilder = __NAMESPACE__ . '\\' . ucfirst($this->config['driver']) . "QueryBuilder";
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
    public function bindQuery($query, array $bindings = null, array $options = []): PDOStatement
    {
        $this->stm = $this->pdo->prepare($query, $options);
        $this->stm->execute($bindings);
        $sqlError = $this->stm->errorInfo();

        if (isset($sqlError[1])) {
            throw new SqlErrorException("Sql error code($sqlError[0]/$sqlError[1]) Error: $sqlError[2]", E_ERROR);
        }

        return $this->stm;
    }


    /**
     * Eşleşen tüm satırları döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return array
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
     * @param Closure $callback
     * @param int $attempts
     * @return mixed
     * @throws Exception
     */
    public function transaction(Closure $callback, int $attempts = 5)
    {
        for($i = 1; $i <= $attempts; $i++) {

            $this->beginTransaction();

            try {
                $result = $callback();
                $this->commit();
                return $result;
            } catch (Exception $e) {

                if($this->stm->errorInfo()[0] == 40001){
                    $this->rollBack();
                    usleep(10);
                    continue;
                }

                throw $e;
            }
        }

        throw new SqlErrorException("$attempts kez denendi ama deadlock çözülemedi.", E_WARNING);
    }

    /**
     * PDO::beginTransaction()
     */
    public function beginTransaction()
    {
        if($this->transectionCount > 0){
            $this->pdo->exec("SAVEPOINT LEVEL{$this->transectionCount}");
        }else{
            $this->pdo->beginTransaction();
        }

        $this->transectionCount++;
    }

    /**
     * PDO::commit()
     */
    public function commit()
    {
        $this->transectionCount--;

        if($this->transectionCount > 0) {
            $this->pdo->exec("RELEASE SAVEPOINT LEVEL{$this->transectionCount}");
        }else{
            $this->pdo->commit();
        }
    }

    /**
     * PDO::rollBack()
     */
    public function rollBack()
    {
        $this->transectionCount--;

        if($this->transectionCount > 0) {
            $this->pdo->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transectionCount}");
        }else{
            $this->pdo->rollBack();
        }
    }

    /**
     * Aktif PDO nesnesi
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Aktif PDOStatement nesnesi
     * @return PDOStatement
     */
    public function stm(): PDOStatement
    {
        return $this->stm;
    }
}
