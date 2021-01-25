<?php

namespace Core\Database;

use PDO;

interface ConnectionInterface
{

    public function __construct(array $config);

    public function connection(): PDO;
}
