<?php 
/**
 * @Created 30.10.2020 01:29:23
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class UserGroupPerm
 * @package Core\Model
 */


namespace Core\Model;


class UserGroupPerm extends Model {

    protected string $table = 'user_group_perm';
    protected string $pk = 'groupID';

    /**
     * @param int $groupID
     * @param int $permID
     * @return array|bool|int|string
     */
    public function assign(int $groupID, int $permID)
    {
        return self::insert(['groupID' => $groupID, 'permID' => $permID])->run();
    }

    /**
     * @param int $permID
     * @return array|bool|int|string
     */
    public function retrievePermission(int $permID)
    {
        return self::delete('permID', $permID)->run();
    }

    /**
     * @param int $groupID
     * @return array|bool|int|string
     */
    public function retrieveGroup(int $groupID)
    {
        return self::delete($groupID)->run();
    }
}
