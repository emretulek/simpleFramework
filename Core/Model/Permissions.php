<?php 
/**
 * @Created 29.10.2020 23:32:57
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Permission
 * @package Core\Model
 */


namespace Core\Model;


class Permissions extends Model{

    protected string $table = 'permissions';
    protected string $pk = 'permissionID';
}
