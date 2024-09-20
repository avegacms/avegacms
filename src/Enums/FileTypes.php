<?php
declare(strict_types=1);
namespace AvegaCms\Enums;

enum FileTypes: string
{
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true], true) ?
            array_column(FileTypes::cases(), $key) : FileTypes::cases();
    }
    case Directory = 'DIRECTORY';
    case Image     = 'IMAGE';
    case Video     = 'VIDEO';
    case Audio     = 'AUDIO';
    case File      = 'FILE';
    case Link      = 'LINK';
    case VideoLink = 'VIDEO_LINK';
}
