<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

class Migrator
{
    /**
     * @var array|list<string>
     */
    public static array $attributes = [
        'ENGINE'  => 'InnoDB',
        'CHARSET' => 'utf8mb4',
        'COLLATE' => 'utf8mb4_unicode_ci',
    ];

    /**
     * @return list<array>
     */
    public static function dateFields(array $exclude = []): array
    {
        $dateList = [
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ];

        if (! empty($exclude)) {
            for ($i = 0; $i < count($exclude); $i++) {
                if (isset($dateList[$exclude[$i]])) {
                    unset($dateList[$exclude[$i]]);
                }
            }
        }

        return $dateList;
    }

    /**
     * @return list<array>
     */
    public static function byId(): array
    {
        return [
            'created_by_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0, 'default' => 0],
            'updated_by_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => 0, 'default' => 0],
        ];
    }
}
