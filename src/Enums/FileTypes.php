<?php

namespace AvegaCms\Enums;

enum FileTypes: string
{
    case Directory = 'DIRECTORY';
    case Image     = 'IMAGE';

    case File      = 'FILE';
    case Link      = 'LINK';
    case VideoLink = 'VIDEO_LINK';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(FileTypes::cases(), $key) : FileTypes::cases();
    }
}