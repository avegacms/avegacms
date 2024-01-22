<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities\Exceptions;

use Exception;

class EmailSenderException extends Exception
{
    /**
     * @return EmailSenderException
     */
    public static function forTemplateNotFound(): EmailSenderException
    {
        return new static(lang('EmailTemplate.errors.templateNotFound'));
    }

    /**
     * @return EmailSenderException
     */
    public static function forNoRecipient(): EmailSenderException
    {
        return new static(lang('EmailTemplate.errors.noRecipient'));
    }

    /**
     * @return EmailSenderException
     */
    public static function forNoEmailFolder(): EmailSenderException
    {
        return new static(lang('EmailTemplate.errors.noEmailFolder'));
    }

    /**
* @param string $file
* @return EmailSenderException
     */
    public static function forNoViewTemplate(string $file): EmailSenderException
    {
        return new static(lang('EmailTemplate.errors.forNoViewTemplate', [$file]));
    }
}