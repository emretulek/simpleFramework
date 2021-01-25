<?php

namespace Core\Model;

use Exception;

class RolePermissions extends Model
{
    protected string $table = 'role_permissions';
    protected string $pk = 'roleID';

    /**
     * @param int $permID
     * @param int $roleID
     * @return array|bool|int|string
     */
    public function assign(int $permID, int $roleID,)
    {
        try {
            self::insert(['roleID' => $roleID, 'permissionID' => $permID]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int $permID
     * @param int|null $roleID
     * @return array|bool|int|string
     */
    public function retrievePermission(int $permID, int $roleID = null)
    {
        if ($roleID) {
            return self::delete(['roleID' => $roleID, 'permissionID' => $permID]);
        } else {
            return self::delete(['permissionID' => $permID]);
        }
    }


    /**
     * @param int $roleID
     * @return array|bool|int|string
     */
    public function retrieveAll(int $roleID)
    {
        return self::delete(['roleID' => $roleID]);
    }
}
