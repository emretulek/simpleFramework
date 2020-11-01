<?php
/**
 * @Created 30.10.2020 01:27:36
 * @Project index.php
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class UserGroups
 * @package Core\Model
 */


namespace Core\Model;


class UserGroups extends Model
{

    protected string $table = 'user_groups ug';
    protected string $pk = 'groupID';

    /**
     * permissions list for each group
     * @return array|bool
     */
    public function getGroupPermissions()
    {
        return self::select("ug.*, up.*")
            ->leftJoin('user_group_perm ugp', 'ug.groupID = ugp.groupID')
            ->leftJoin('user_permissions up', 'up.permID = ugp.permID')
            ->get();
    }

    /**
     * @param int $groupID
     * @return array|bool
     */
    public function getGroupPermissionsFromID(int $groupID)
    {
        return self::select("ug.*, up.*")
            ->leftJoin('user_group_perm ugp', 'ug.groupID = ugp.groupID')
            ->leftJoin('user_permissions up', 'up.permID = ugp.permID')
            ->where('ug.groupID', $groupID)
            ->get();
    }

    /**
     * @param string $groupName
     * @return array|bool
     */
    public function getGroupPermissionsFromGroupName(string $groupName)
    {
        return self::select("ug.*, up.*")
            ->leftJoin('user_group_perm ugp', 'ug.groupID = ugp.groupID')
            ->leftJoin('user_permissions up', 'up.permID = ugp.permID')
            ->where('ug.groupName', $groupName)
            ->get();
    }
}
