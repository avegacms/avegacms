<?php

declare(strict_types=1);

namespace AvegaCms\Exceptions;

use Exception;

class AvegaCmsException extends Exception
{
    protected array|string $messages = [];

    public function __construct(array|string $message, int $code = 400)
    {
        $this->messages = $message;

        parent::__construct(message: lang('Api.errors.validationError'), code: $code);
    }

    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    public static function forNoData(): AvegaCmsException
    {
        return new static(message: lang('Api.errors.noData'));
    }

    public static function forInvalidJSON(?string $error = null): AvegaCmsException
    {
        return new static(message: lang('Api.errors.invalidJSON', [$error]));
    }

    public static function forUndefinedData(): AvegaCmsException
    {
        return new static(message: lang('Api.errors.undefinedData'));
    }
}
