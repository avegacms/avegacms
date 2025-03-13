<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Content\Exceptions;

use Exception;

class ContentExceptions extends Exception
{
    protected array|string $messages = [];

    public function __construct(array|string $messages, int $code = 400)
    {
        $this->messages = $messages;

        parent::__construct(message: lang('Api.errors.validationError'), code: $code);
    }

    /**
     * @return array|list<array>|list<string>
     */
    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    public static function forNoData(): ContentExceptions
    {
        return new static(lang('Api.errors.noData'));
    }

    public static function forNoModuleId(): ContentExceptions
    {
        return new static(lang('Api.errors.content.noModuleId'));
    }

    public static function forForbiddenPageDelete(): ContentExceptions
    {
        return new static(lang('Api.errors.content.forbiddenPageDelete'));
    }

    public static function forUnknownType(): ContentExceptions
    {
        return new static(lang('Api.errors.content.unknownType'));
    }

    public static function unknownPatchMethod(): ContentExceptions
    {
        return new static(lang('Api.errors.content.unknownPatchMethod'));
    }
}
