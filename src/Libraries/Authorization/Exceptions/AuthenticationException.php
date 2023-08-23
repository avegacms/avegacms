<?php

namespace AvegaCms\Libraries\Authorization\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class AuthenticationException extends FrameworkException
{
    /**
     * @return AuthenticationException
     */
    public static function forNoSettings(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.noSettings'));
    }

    /**
     * @return AuthenticationException
     */
    public static function forNoHeaderAuthorize(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.noHeaderAuthorize'), code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forExpiredToken(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.expiresToken'), code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forTokenNotFound(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.tokenNotFound'), code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forNotAuthorized(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.notAuthorized'), code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forUserSessionNotExist(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.userSessionNotExist'), code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forAccessDenied(): AuthenticationException
    {
        return new static(message: 'Access denied', code: 401);
    }

    /**
     * @return AuthenticationException
     */
    public static function forUnknownPermission(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.unknownPermission'), code: 403);
    }

    /**
     * @return AuthenticationException
     */
    public static function forForbiddenAccess(): AuthenticationException
    {
        return new static(message: lang('Authentication.errors.forbiddenAccess'), code: 403);
    }

}