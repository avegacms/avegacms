<?php

namespace AvegaCms\Enums;

enum MetaStatuses: string
{
    case Publish   = 'PUBLISH';
    case Future    = 'FUTURE';
    case Moderated = 'MODERATED';
    case Draft     = 'DRAFT';
    case Trash     = 'TRASH';

    public static function getValues(): array
    {
        return array_column(MetaStatuses::cases(), 'value');
    }
}