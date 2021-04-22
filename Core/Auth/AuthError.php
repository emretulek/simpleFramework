<?php


namespace Core\Auth;


use Exception;

class AuthError extends Exception
{
    /**
     * @const Kullanıcı bulunamadı
     */
    const USER_NOT_FOUND = 1;

    /**
     * @const Şifre Eşleşmedi
     */
    const PASSWORD_MISMATCH = 2;
}
