<?php

declare(strict_types = 1);

namespace AvegaCms\Database\Factories;

use AvegaCms\Models\Admin\ContentModel;
use Faker\Generator;

class MetaContentFactory extends ContentModel
{
    public function fake(Generator &$faker): array
    {
        return [
            'id'      => 0,
            'anons'   => $faker->paragraph(1),
            'content' => $faker->paragraph(rand(6, 36))
        ];
    }
}
