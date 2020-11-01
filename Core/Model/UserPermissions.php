<?php 
/**
 * @Created 29.10.2020 23:32:57
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Permission
 * @package Core\Model
 */


namespace Core\Model;


class UserPermissions extends Model{

    protected string $table = 'user_permissions';
    protected string $pk = 'permID';
}
