<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities\Exceptions;

use Exception;

class UploaderException extends Exception
{
    protected array|string $messages = [];

    /**
     * @param  array|string  $messages
     */
    public function __construct(array|string $messages)
    {
        $this->messages = $messages;

        parent::__construct(message: lang('Authorization.errors.validationError'), code: 400);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    /**
     * @return UploaderException
     */
    public static function forEmptyPath(): UploaderException
    {
        return new static(lang('Uploader.errors.emptyPath'));
    }

    /**
     * @param  string  $directory
     * @return UploaderException
     */
    public static function forCreateDirectory(string $directory): UploaderException
    {
        return new static(lang('Uploader.errors.createDirectory', [$directory]));
    }

    /**
     * @param  string  $file
     * @return UploaderException
     */
    public static function forHasMoved(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.hasMoved', [$file]));
    }
}