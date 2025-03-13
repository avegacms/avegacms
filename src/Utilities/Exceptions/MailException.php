<?php

declare(strict_types=1);

namespace AvegaCms\Utilities\Exceptions;

use Exception;

class MailException extends Exception
{
    public static function forTemplateNotFound(): MailException
    {
        return new static(lang('EmailTemplate.errors.templateNotFound'));
    }

    public static function forNoRecipient(): MailException
    {
        return new static(lang('EmailTemplate.errors.noRecipient'));
    }

    public static function forNoEmailFolder(): MailException
    {
        return new static(lang('EmailTemplate.errors.noEmailFolder'));
    }

    public static function forNoViewTemplate(string $file): MailException
    {
        return new static(lang('EmailTemplate.errors.forNoViewTemplate', [$file]));
    }

    public static function forNoSendEmail(): MailException
    {
        return new static(lang('EmailTemplate.errors.noSendEmail'));
    }
}
