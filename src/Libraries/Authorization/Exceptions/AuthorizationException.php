<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class AuthorizationException extends FrameworkException
{
    /**
     * @return AuthorizationException
     */
    public static function forNoData(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.noData'));
    }

    /**
     * @param  string|null  $type
     * @return AuthorizationException
     */
    public static function forUnknownAuthType(string $type = null): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.unknownAuthType', [$type]));
    }

    /**
     * @param  string|null  $field
     * @return AuthorizationException
     */
    public static function forUnknownLoginField(string $field = null): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.unknownLoginField', [$field]));
    }

    /**
     * @param  string|null  $role
     * @return AuthorizationException
     */
    public static function forUnknownRole(string $role = null): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.unknownRole', [$role]));
    }

    /**
     * @return AuthorizationException
     */
    public static function forUnknownUser(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.unknownUser'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forWrongPassword(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.wrongPassword'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forFailSendAuthCode(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.failSendAuthCode'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forCreateToken(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.createToken'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forCodeExpired(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.codeExpired'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forWrongCode(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.wrongCode'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forFailPasswordUpdate(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.failPasswordUpdate'));
    }

    /**
     * @return AuthorizationException
     */
    public static function forUserSessionNotExist(): AuthorizationException
    {
        return new static(message: lang('Authorization.errors.userSessionNotExist'));
    }

    /**
     * @param  string|null  $message
     * @return AuthorizationException
     */
    public static function forFailUnauthorized(?string $message = null): AuthorizationException
    {
        return new static(message: is_null($message) ? '' : lang('Authorization.errors.' . $message), code: 401);
    }

    /**
     * @param  string|null  $message
     * @return AuthorizationException
     */
    public static function forFailForbidden(?string $message = null): AuthorizationException
    {
        return new static(message: is_null($message) ? '' : lang('Authorization.errors.' . $message), code: 403);
    }
}