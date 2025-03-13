<?php

declare(strict_types=1);

namespace AvegaCms\Utilities\Exceptions;

use Exception;

class UploaderException extends Exception
{
    protected array|string $messages = [];

    public function __construct(array|string $messages)
    {
        $this->messages = $messages;

        parent::__construct(message: lang('Authorization.errors.validationError'), code: 400);
    }

    public function getMessages(): array
    {
        return ! is_array($this->messages) ? [$this->messages] : $this->messages;
    }

    public static function forDirectoryNotFound(string $directory): UploaderException
    {
        return new static(lang('Uploader.errors.directoryNotFound', [$directory]));
    }

    public static function forFileNotFound(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.fileNotFound', [$file]));
    }

    public static function forEmptyPath(): UploaderException
    {
        return new static(lang('Uploader.errors.emptyPath'));
    }

    public static function forGDLibNotSupported(): UploaderException
    {
        return new static(lang('Uploader.errors.gdLibNotSupported'));
    }

    public static function forCreateDirectory(string $directory): UploaderException
    {
        return new static(lang('Uploader.errors.createDirectory', [$directory]));
    }

    public static function forHasMoved(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.hasMoved', [$file]));
    }

    public static function forNotMovedFile(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.notMovedFile', [$file]));
    }

    public static function forUnsupportedImageFormat(string $mime): UploaderException
    {
        return new static(lang('Uploader.errors.unsupportedImageFormat', [$mime]));
    }

    public static function forFiledToConvertImageToWebP(string $message): UploaderException
    {
        return new static(lang('Uploader.errors.filedToConvertImageToWebP', [$message]));
    }

    public static function forFailThumbCreated(string $message): UploaderException
    {
        return new static(lang('Uploader.errors.failThumbCreated', [$message]));
    }
}
