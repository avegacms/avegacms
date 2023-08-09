<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Authorization\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $messages = [];

    /**
     * @param array $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;

        parent::__construct(message: lang('Authorization.errors.validationError'), code: 400);
    }

    /**
     * @param string|null $rule
     * @return ValidationException
     */
    public static function forRulesNotFound(): ValidationException
    {
        return new static([lang('Authorization.errors.rulesNotFound')]);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}