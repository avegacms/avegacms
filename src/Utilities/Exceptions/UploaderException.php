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
     * @param  string  $directory
     * @return UploaderException
     */
    public static function forDirectoryNotFound(string $directory): UploaderException
    {
        return new static(lang('Uploader.errors.directoryNotFound', [$directory]));
    }

    /**
     * @param  string  $file
     * @return UploaderException
     */
    public static function forFileNotFound(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.fileNotFound', [$file]));
    }

    /**
     * @return UploaderException
     */
    public static function forEmptyPath(): UploaderException
    {
        return new static(lang('Uploader.errors.emptyPath'));
    }

    /**
     * @return UploaderException
     */
    public static function forGDLibNotSupported(): UploaderException
    {
        return new static(lang('Uploader.errors.gdLibNotSupported'));
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

    /**
     * @param  string  $file
     * @return UploaderException
     */
    public static function forNotMovedFile(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.notMovedFile', [$file]));
    }

    public static function forUnsupportedImageFormat(string $mime): UploaderException
    {
        return new static(lang('Uploader.errors.unsupportedImageFormat', [$mime]));
    }

    /**
     * @param  string  $message
     * @return UploaderException
     */
    public static function forFiledToConvertImageToWebP(string $message): UploaderException
    {
        return new static(lang('Uploader.errors.filedToConvertImageToWebP', [$message]));
    }

    /**
     * @param  string  $message
     * @return UploaderException
     */
    public static function forFailThumbCreated(string $message): UploaderException
    {
        return new static(lang('Uploader.errors.failThumbCreated', [$message]));
    }
}