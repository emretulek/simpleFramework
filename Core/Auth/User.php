<?php 
/**
 * @Created 27.06.2020 22:31:27
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class User
 * @package Core\AuthServices
 */


namespace Core\Auth;


class User {

    public int $id;
    public string $username;
    public string $password;
    public string $group;
    public array $info = [];

    public function __construct(int $id, string $username, string $password, string $group, array $info = [])
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->group = $group;
        $this->info = $info;
    }
}
