<?php

namespace Core\Facades;

/**
 * @see \Core\Auth\Auth::loginWithUserName()
 * @method static bool loginWithUserName(string $password, string $userName, $remember = 0, array $where = [])
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::loginWithEmail()
 * @method static bool loginWithEmail(string $password, string $email, $remember = 0, array $where = [])
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::loginWithEmailOrUserName()
 * @method static bool loginWithEmailOrUserName(string $password, string $userNameOrEmail, $remember = 0, array $where = [])
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::login()
 * @method static bool login($password = null, $userInfo = [], $remember = 0)
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::rememberMe()
 * @method static bool rememberMe()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::check()
 * @method static bool check()
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::user()
 * @method static object|null user()
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::authSession()
 * @method static mixed authSession($key, $value = null)
 * ----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::userID()
 * @method static int userID()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::roleName()
 * @method static null|string roleName()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::role()
 * @method static bool|int role()
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::guard()
 * @method static bool guard($groupName)
 * -----------------------------------------------------------------------------
 * @see \Core\Auth\Auth::permission()
 * @method static bool permission($permissions)
 * -----------------------------------------------------------------------------
 * @method static void logout(bool $clear_all = false)
 * @see \Core\Auth\Auth::logout()
 * ----------------------------------------------------------------------------
 * @mixin \Core\Auth\Auth
 * @see \Core\Auth\Auth
 */
class Auth extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Auth\Auth::class;
    }
}
