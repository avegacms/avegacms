<?php

namespace AvegaCms\Enums;

enum FileProviders: string
{
    case Local = 'LOCAL';
    case Cdn   = 'CDN';
    case Cloud = 'CLOUD';
    
    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(FileProviders::cases(), $key) : FileProviders::cases();
    }
}
