<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Uploader\Exceptions;

use Exception;

class UploaderException extends Exception
{
    protected array $messages = [];

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
        return $this->messages;
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
    public static function forCreateDirectory(string $directory): UploaderException
    {
        return new static(lang('Uploader.errors.createDirectory', [$directory]));
    }

    public static function forHasMoved(string $file): UploaderException
    {
        return new static(lang('Uploader.errors.hasMoved', [$file]));
    }
}