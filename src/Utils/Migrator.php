<?php

namespace AvegaCms\Utils;

class Migrator
{
    /**
     * @var array|string[]
     */
    public static array $attributes = [
        'ENGINE'  => 'InnoDB',
        'CHARSET' => 'utf8',
        'COLLATE' => 'utf8_unicode_ci'
    ];

    /**
     * @param  array  $exclude
     * @return array[]
     */
    public static function dateFields(array $exclude = []): array
    {
        $dateList = [

            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true]
        ];

        if ( ! empty($exclude)) {
            for ($i = 0; $i < count($exclude); $i++) {
                if (isset($dateList[$exclude[$i]])) {
                    unset($dateList[$exclude[$i]]);
                }
            }
        }

        return $dateList;
    }

    /**
     * @return array[]
     */
    public static function byId(): array
    {
        return [
            'created_by_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0, 'default' => 0],
            'updated_by_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0, 'default' => 0]
        ];
    }
}