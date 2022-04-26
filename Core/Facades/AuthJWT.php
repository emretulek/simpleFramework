<?php

namespace Core\Facades;

/**
 * @see \Core\Auth\AuthJWT::loginWithUserName()
 * @method static string loginWithUserName(string $password, string $userName, $remember = 0, array $where = [])
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::loginWithEmail()
 * @method static string loginWithEmail(string $password, string $email, $remember = 0, array $where = [])
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::loginWithEmailOrUserName()
 * @method static string loginWithEmailOrUserName(string $password, string $userNameOrEmail, $remember = 0, array $where = [])
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::login()
 * @method static string login($password = null, $userInfo = [], $remember = 0)
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::getData()
 * @method static false|object getData()
 * -----------------------------------
 * @see \Core\Auth\AuthJWT::check()
 * @method static bool check()
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::user()
 * @method static object|null user()
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::userID()
 * @method static int userID()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::roleName()
 * @method static null|string roleName()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::role()
 * @method static bool|int role()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::guard()
 * @method static bool guard($groupName)
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\AuthJWT::permission()
 * @method static bool permission($permissions)
 * ----------------------------------------------------------------------------
 * @mixin \Core\Auth\AuthJWT
 * @see \Core\Auth\AuthJWT
 */
class AuthJWT extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Auth\AuthJWT::class;
    }
}
