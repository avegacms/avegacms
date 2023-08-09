<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class AuthorizationExceptions extends FrameworkException
{
    /**
     * @return AuthorizationExceptions
     */
    public static function forNoData(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.noData'));
    }

    /**
     * @param string|null $type
     * @return AuthorizationExceptions
     */
    public static function forUnknownAuthType(string $type = null): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.unknownAuthType', [$type]));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forUnknownLoginField(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.unknownLoginField'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forUnknownUser(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.unknownUser'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forWrongPassword(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.wrongPassword'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forFailSendAuthCode(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.failSendAuthCode'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forCreateToken(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.createToken'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forCodeExpired(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.codeExpired'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forWrongCode(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.wrongCode'));
    }

    /**
     * @return AuthorizationExceptions
     */
    public static function forFailPasswordUpdate(): AuthorizationExceptions
    {
        return new static(message: lang('Authorization.errors.failPasswordUpdate'));
    }

    /**
     * @param string|null $message
     * @return AuthorizationExceptions
     */
    public static function forFailUnauthorized(?string $message = null): AuthorizationExceptions
    {
        return new static(message: is_null($message) ? '' : lang('Authorization.errors.' . $message), code: 401);
    }

    /**
     * @param string|null $message
     * @return AuthorizationExceptions
     */
    public static function forFailForbidden(?string $message = null): AuthorizationExceptions
    {
        return new static(message: is_null($message) ? '' : lang('Authorization.errors.' . $message), code: 403);
    }
}