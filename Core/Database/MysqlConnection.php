<?php 
/**
 * @Created 09.12.2020 00:48:51
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class MysqlConnection
 * @package Core\Database
 */


namespace Core\Database;

use PDO;

class MysqlConnection implements ConnectionInterface {

    private string $driver;
    private string $host;
    private string $port;
    private string $database;
    private string $user;
    private string $password;
    private string $charset;
    private string $collation;
    private array $options;
    private string $dsn;

    public array $config;
    public PDO $pdo;

    public function __construct(array $config)
    {
        $this->driver = $config['driver'];
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->database = $config['database'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->charset = $config['charset'];
        $this->collation = $config['collation'];
        $this->options = $config['options'];

        //set dsn
        $this->dsn = $this->driver.':host='.$this->host;
        $this->dsn .= $this->port ? ';port='.$this->port : '';
        $this->dsn .= ';dbname='.$this->database;
        $this->config = $config;
        $this->config['dsn'] = $this->dsn;
    }

    /**
     * @return PDO
     */
    public function connection(): PDO
    {
        $this->pdo = new PDO($this->dsn, $this->user, $this->password);
        $this->pdo->exec("SET NAMES '" . $this->charset . "' COLLATE '" . $this->collation . "'");

        foreach ($this->options as $optionKey => $optionValue) {
            $this->pdo->setAttribute($optionKey, $optionValue);
        }

        return $this->pdo;
    }
}
