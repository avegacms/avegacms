<?php

namespace AvegaCms\Enums;

enum FileTypes: string
{
    case Image     = 'IMAGE';
    case File      = 'FILE';
    case Link      = 'LINK';
    case VideoLink = 'VIDEO_LINK';

    public static function getValues(): array
    {
        return array_column(FileTypes::cases(), 'value');
    }
}