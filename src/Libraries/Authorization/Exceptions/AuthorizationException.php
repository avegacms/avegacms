<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization\Exceptions;

use Exception;

class AuthorizationException extends Exception
{
    protected array|string $messages = [];

    public function __construct(array|string $messages, int $code = 400)
    {
        $this->messages = $messages;

        parent::__construct(message: lang('Authorization.errors.validationError'), code: $code);
    }

    public static function forRulesNotFound(): AuthorizationException
    {
        return new static([lang('Authorization.errors.rulesNotFound')]);
    }

    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    public static function forNoData(): AuthorizationException
    {
        return new static(lang('Authorization.errors.noData'));
    }

    public static function forUnknownAuthType(?string $type = null): AuthorizationException
    {
        return new static(lang('Authorization.errors.unknownAuthType', [$type]));
    }

    public static function forUnknownLoginField(?string $field = null): AuthorizationException
    {
        return new static(lang('Authorization.errors.unknownLoginField', [$field]));
    }

    public static function forUnknownRole(?string $role = null): AuthorizationException
    {
        return new static(lang('Authorization.errors.unknownRole', [$role]));
    }

    public static function forUnknownUser(): AuthorizationException
    {
        return new static(lang('Authorization.errors.unknownUser'));
    }

    public static function forWrongPassword(): AuthorizationException
    {
        return new static(lang('Authorization.errors.wrongPassword'));
    }

    public static function forFailSendAuthCode(): AuthorizationException
    {
        return new static(lang('Authorization.errors.failSendAuthCode'));
    }

    public static function forCreateToken(): AuthorizationException
    {
        return new static(lang('Authorization.errors.createToken'));
    }

    public static function forCodeExpired(): AuthorizationException
    {
        return new static(lang('Authorization.errors.codeExpired'));
    }

    public static function forWrongCode(): AuthorizationException
    {
        return new static(lang('Authorization.errors.wrongCode'));
    }

    public static function forFailPasswordUpdate(): AuthorizationException
    {
        return new static(lang('Authorization.errors.failPasswordUpdate'));
    }

    public static function forUserSessionNotExist(): AuthorizationException
    {
        return new static(lang('Authorization.errors.userSessionNotExist'));
    }

    public static function forFailUnauthorized(?string $message = null): AuthorizationException
    {
        return new static(null === $message ? '' : lang('Authorization.errors.' . $message), 401);
    }

    public static function forFailForbidden(?string $message = null): AuthorizationException
    {
        return new static(null === $message ? '' : lang('Authorization.errors.' . $message), 403);
    }
}
