<?php

namespace AvegaCms\Enums;

enum FileProviders: string
{
    case Local = 'LOCAL';
    case Cdn   = 'CDN';
    case Cloud = 'CLOUD';

    /**
     * @return array
     */
    public static function getValues(): array
    {
        return array_column(FileProviders::cases(), 'value');
    }
}
