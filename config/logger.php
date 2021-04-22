<?php
return [
    'driver' => 'file',
    //dosya adı
    'file' => [
        'file' => ROOT.'/storage/logs/logger.log'
    ],
    //veritabanı tablosu
    'database' => [
        'table' => 'logger'
    ],
    //null driver logger disable
    'null' => 'null'
];
