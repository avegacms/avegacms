<?php

declare(strict_types = 1);

namespace AvegaCms\Libraries\Content\Exceptions;

use Exception;

class ContentExceptions extends Exception
{
    protected array|string $messages = [];

    /**
     * @param  array|string  $messages
     * @param  int  $code
     */
    public function __construct(array|string $messages, int $code = 400)
    {
        $this->messages = $messages;
        
        parent::__construct(message: lang('Api.errors.validationError'), code: $code);
    }

    /**
     * @return array|array[]|string[]
     */
    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    /**
     * @return ContentExceptions
     */
    public static function forNoData(): ContentExceptions
    {
        return new static(lang('Api.errors.noData'));
    }

    /**
     * @return ContentExceptions
     */
    public static function forNoModuleId(): ContentExceptions
    {
        return new static(lang('Api.errors.content.noModuleId'));
    }

    /**
     * @return ContentExceptions
     */
    public static function forUnknownType(): ContentExceptions
    {
        return new static(lang('Api.errors.content.unknownType'));
    }

}