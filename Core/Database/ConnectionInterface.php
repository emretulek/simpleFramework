<?php 
/**
 * @Created 09.12.2020 01:22:53
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class ConnectionInterface
 * @package Core\Database
 */


namespace Core\Database;


use PDO;

interface ConnectionInterface {

    public function __construct(array $config);
    public function connection():PDO;
}
