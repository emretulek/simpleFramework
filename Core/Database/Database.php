<?php 
/**
 * @Created 18.05.2020 08:09:39
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Database
 * @package Core\Database
 */


namespace Core\Database;

use Core\Exceptions\Exceptions;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database {

    private ?PDO $pdo;
    private ?PDOStatement $stm;

    private string $driver;
    private string $host;
    private string $database;
    private string $user;
    private string $password;
    private string $charset;
    private string $collection;


    /**
     * Database constructor.
     * @param $driver
     * @param $host
     * @param $database
     * @param $user
     * @param $password
     * @param string $charset
     * @param string $collaction
     * @throws Exception
     */
    public function __construct(string $driver, string $host, string $database, string $user, string $password, string $charset = 'utf8', string $collaction = 'utf8_general_ci')
    {
        $this->driver = $driver;
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        $this->charset = $charset;
        $this->collection = $collaction;

        $this->connect();
    }

    /**
     * Veritabanı bağlantısı oluşturur
     * @throws Exception
     */
    private function connect()
    {
        try {
            $this->pdo = new PDO($this->driver.':host='.$this->host.';dbname='.$this->database, $this->user, $this->password);
            $this->pdo->exec("SET NAMES '" . $this->charset . "' COLLATE '" . $this->collection . "'");
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            throw new Exception($this->driver." veritabanı bağlantısı kurulamıyor.", E_ERROR, $e);
        }

        return $this;
    }

    /**
     * Yeniden database seçer
     * @param $database
     * @return $this
     * @throws Exception
     */
    public function selectDB(string $database)
    {
        if($this->pdo->exec("USE $database") === false){
            throw new Exception("Veritabanı seçilemedi.", E_ERROR);
        }

        return $this;
    }


    /**
     * Sorgu ve değişkenleri bağlar
     * @param $query
     * @param array|null $bindings
     * @param array $options
     * @return $this
     * @throws Exception
     */
    public function bindQuery($query, array $bindings = null, array $options = [])
    {
        try {
            $this->stm = $this->pdo->prepare($query, $options);
            $this->stm->execute($bindings);
            $sqlError = $this->stm->errorInfo();

            if(isset($sqlError[1])){

                throw new Exception("Sql error code({$sqlError[0]}/{$sqlError[1]}) Error: {$sqlError[2]}", E_ERROR);
            }
        }catch (PDOException $e){
            Exceptions::debug($e, 2);
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }

        return $this;
    }


    /**
     * Eşleşen tüm satırları döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return array|bool
     */
    public function get(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->fetchAll($fetchStyle);
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * Eşleşen ilk satırı döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return bool|mixed
     */
    public function getRow(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_OBJ)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->fetch($fetchStyle);
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * Sorgu sonucu dönen ilk stunun tamanını döndürür
     * @param string $query
     * @param array|null $bindings
     * @param int $fetchStyle
     * @return array|bool
     */
    public function getCol(string $query, array $bindings = null, $fetchStyle = PDO::FETCH_COLUMN)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->fetchAll($fetchStyle);
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * Eşleşen ilk satırdan belirtilen stunu döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|mixed
     */
    public function getVar(string $query, array $bindings = null)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->fetchColumn();
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * İnsert edilen son satırın autoincrement değerini döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|string
     */
    public function insert(string $query, array $bindings = null)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->pdo->lastInsertId();
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * Update işleminden etkilenen satır sayısını döndürür,
     * etkilenen satır yoksa true, hata oluşursa false döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     */
    public function update(string $query, array $bindings = null)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->rowCount() ? $this->stm->rowCount() : true;
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }


    /**
     * Silme işleminden etkilenen satır sayısını döndürür
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     */
    public function delete(string $query, array $bindings = null)
    {
        try {
            $this->bindQuery($query, $bindings);
            return $this->stm->rowCount();
        }catch (Exception $e){
            Exceptions::debug($e, 2);
        }
        return false;
    }

    /**
     * Aktif veritabanı bağlantısını sonlandırır
     */
    public function close()
    {
        $this->pdo = null;
        $this->stm = null;
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
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Aktif PDOStatement nesnesi
     * @return PDOStatement
     */
    public function stm()
    {
        return $this->stm;
    }
}
