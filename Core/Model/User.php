<?php


namespace Core\Model;


class User extends Model
{
    protected string $table = 'users';
    protected string $pk = 'userID';

    protected array $messages = [
        'username' => 'Bu kullanıcı adı kullanılıyor.',
        'email' => 'Bu e-posta hesabı kullanılıyor.'
    ];

    const STATUS = ['0', '1'];

    /**
     * @return \Core\Database\QueryBuilder
     */
    public function getUsersWithRole()
    {
        return User::table('users u', true)->join('user_roles ur', 'ur.roleID = u.roleID')
            ->isNull('u.deleted_at')
            ->select();
    }

    /**
     * @param $columns
     * @return array|bool|int
     * @throws ModelException
     * @throws \Core\Database\SqlErrorException
     */
    public function store($columns)
    {
        if(isset($columns['username'])){
            if(User::select('userID')->where('username', $columns['username'])->getVar()){
                return $this->setError($this->messages['username'], 'username');
            }
        }

        if(User::select('userID')->where('email', $columns['email'])->getVar()){
            return $this->setError($this->messages['email'], 'email');
        }

        return User::insert($columns);
    }


    /**
     * @param $userID
     * @param $columns
     * @return array|bool|int
     * @throws ModelException
     * @throws \Core\Database\SqlErrorException
     */
    public function edit($userID, $columns)
    {
        if(isset($columns['username'])){
            if(User::select('userID')->where('username', $columns['username'])
                ->where('userID', '<>', $userID)->getVar()){
                return $this->setError($this->messages['username'], 'username');
            }
        }

        if(User::select('userID')->where('email', $columns['email'])
            ->where('userID', '<>', $userID)->getVar()){
            return $this->setError($this->messages['email'], 'email');
        }

        return User::where('userID', $userID)->update($columns);
    }
}
