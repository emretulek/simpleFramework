<?php

namespace Core\Model;

class Roles extends Model
{
    protected string $table = 'user_roles r';
    protected string $pk = 'r.roleID';

    /**
     * permissions list for each group
     * @return array|bool
     */
    public function getRolePermissions()
    {
        return self::select("r.*, p.*")
            ->leftJoin('role_permissions rp', 'r.roleID = rp.roleID')
            ->leftJoin('permissions p', 'p.permissionID = rp.permissionID')
            ->get();
    }

    /**
     * @param int $roleID
     * @return array|bool
     */
    public function getRoleFromID(int $roleID)
    {
        return self::select("p.*")
            ->leftJoin('role_permissions rp', 'rp.roleID = r.roleID')
            ->leftJoin('permissions p', 'p.permissionID = rp.permissionID')
            ->where('r.roleID', $roleID)
            ->get();
    }

    /**
     * @param string $roleName
     * @return array|bool
     */
    public function getRoleFromName(string $roleName)
    {
        return self::select("p.*")
            ->leftJoin('role_permissions rp', 'rp.roleID = r.roleID')
            ->leftJoin('permissions p', 'p.permissionID = rp.permissionID')
            ->where('r.role_name', $roleName)
            ->get();
    }
}
