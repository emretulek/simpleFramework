<?php

namespace Core\Database;

use Core\Exceptions\Exceptions;
use Exception;
use PDO, PDOException;
use Core\Config\Config;

/**
 * Class Database
 * PDO static veritabanı sınıfı.
 */
class Database
{
    private static $pdo = null;
    private static $stmt;

    public static $rowCount;
    public static $insertId;


    private static function instance()
    {
        try {
            if (self::$pdo === null) self::$pdo = self::connect();
        }catch (Exception $e){
            Exceptions::debug($e);
        }
        return self::$pdo;
    }

    /**
     * PDO bağlantı methodu
     *
     * @param null $driver
     * @param null $dsn
     * @param null $user
     * @param null $password
     * @param null $charset
     * @param null $collaction
     * @return PDO
     * @throws Exception
     */
    public static function connect($driver = null, $dsn = null, $user = null, $password = null, $charset = null, $collaction = null)
    {
        /* Ayarlar */
        $driver = $driver ? $driver : Config::get('app.sql_driver');
        $dsn = $dsn ? $dsn : Config::get('database.' . $driver . '.dsn');
        $user = $user ? $user : Config::get('database.' . $driver . '.user');
        $password = $password ? $password : Config::get('database.' . $driver . '.password');
        $charset = $charset ? $charset : Config::get('database.' . $driver . '.charset');
        $collaction = $collaction ? $collaction : Config::get('database.' . $driver . '.collaction');

        try {
            $pdo = new PDO($dsn, $user, $password);
            $pdo->exec("SET NAMES '" . $charset . "' COLLATE '" . $collaction . "'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        } catch (PDOException $exception) {
            /* bağlantı başarısız olursa hata LogExcepiton sınıfına taşınır */
            throw new Exception("Can not connect to " . $driver . " server.", E_ERROR, $exception);
        }
        return $pdo;
    }


    /**
     * config/database.php dosyasında tanımlı farkı drivera geçiş yapar.
     *
     * @param string $driver
     */
    public static function selectDriver(string $driver)
    {
        Config::set('app.sqlDriver', $driver);
        self::close();
    }


    /**
     * Aynı sql sunucusunda farklı bir veritabanına geçiş yapar.
     * @param string $databaseName
     * @return bool
     */
    public static function selectDatabase(string $databaseName)
    {
        if (self::instance()->exec("USE $databaseName") === false) {
            return false;
        }
        return true;
    }


    /**
     * @param string $query
     * @param array|null $bindings
     * @return bool|\PDOStatement
     * @throws Exception
     */
    private static function query(string $query, array $bindings = null)
    {
        self::$stmt = self::instance()->prepare($query);
        self::$stmt->execute($bindings);
        $error = self::$stmt->errorInfo();

        if ($error[2]) {
            throw new Exception("Sql error code({$error[0]}/{$error[1]}) Error: {$error[2]}", E_WARNING);
        }

        return self::$stmt;
    }


    /**
     * Sorgudan dönen tüm sonuçları object olarak döndürür.
     *
     * @param string $query
     * @param array|null $bindings
     * @return array|bool
     */
    public static function get(string $query, array $bindings = null)
    {
        try {
            if (self::$stmt = self::query($query, $bindings)) {
                $result = self::$stmt->fetchAll();
                self::$rowCount = count($result);
                return $result;
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }


    /**
     * Sorgudan dönen ilk satırı object olarak döndürür
     *
     * @param string $query
     * @param array|null $bindings
     * @return bool|mixed
     */
    public static function getRow(string $query, array $bindings = null)
    {
        try {
            if (self::$stmt = self::query($query, $bindings)) {
                return self::$stmt->fetch();
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }


    /**
     * Sorgudan dönen ilk satırın ilk stununu string olarak döndürür.
     *
     * @param string $query
     * @param array|null $bindings
     * @return bool|mixed
     */
    public static function getVar(string $query, array $bindings = null)
    {
        try {
            if (self::$stmt = self::query($query, $bindings)) {
                return self::$stmt->fetchColumn();
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }


    /**
     * Sorgu başarılı ise eklenen son id'yi döndürür.
     *
     * @param string $query
     * @param array|null $bindings
     * @return bool|string
     */
    public static function insert(string $query, array $bindings = null)
    {
        try {
            if (self::query($query, $bindings)) {
                return self::$insertId = self::instance()->lastInsertId();
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }


    /**
     * Sorgudan etkilenen satır sayısını döndürür.
     *
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     */
    public static function update(string $query, array $bindings = null)
    {
        try {
            if (self::$stmt = self::query($query, $bindings)) {
                return self::$rowCount = self::$stmt->rowCount();
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }


    /**
     * Sorgudan etkilenen satır sayısını döndürür. update methodu ile eşdeğerdir.
     *
     * @param string $query
     * @param array|null $bindings
     * @return bool|int
     */
    public static function delete(string $query, array $bindings = null)
    {
        try {
            if (self::$stmt = self::query($query, $bindings)) {
                return self::$rowCount = self::$stmt->rowCount();
            }
        }catch (Exception $e){
            Exceptions::debug($e, 1);
        }
        return false;
    }

    /**
     * DB sınıfında bulunmayan diğer PDO methodlarına erişim sağlar.
     *
     * @param string $name çağırılan methodun adı.
     * @param array $arguments methodun alacağı parametreler.
     * @return mixed başarılı ise sorgu sonucunu değilse false döndürür.
     */
    public static function __callStatic(string $name, $arguments = array())
    {
        return call_user_func_array([self::instance(), $name], $arguments);
    }


    /**
     * Veritabanı sınıfını sonlandırır.
     */
    public static function close()
    {
        self::$pdo = null;
    }
}
