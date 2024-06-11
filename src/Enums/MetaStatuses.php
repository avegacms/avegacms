<?php

namespace AvegaCms\Enums;

enum MetaStatuses: string
{
    case Publish   = 'PUBLISH';
    case Future    = 'FUTURE';
    case Moderated = 'MODERATED';
    case Draft     = 'DRAFT';
    case Trash     = 'TRASH';

    /**
     * @param  string|null  $key
     * @return array
     */
    public static function get(?string $key = null): array
    {
        return in_array($key, ['name', 'value', true]) ?
            array_column(MetaStatuses::cases(), $key) : MetaStatuses::cases();
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $list = [];
        foreach (MetaStatuses::cases() as $enum) {
            $list[] = ['label' => lang('Enums.MetaStatuses.' . $enum->name), 'value' => $enum->value];
        }
        return $list;
    }
}