<?php

declare(strict_types=1);

namespace AvegaCms\Database\Factories;

use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Models\Admin\MetaDataModel;
use Faker\Generator;

class MetaDataFactory extends MetaDataModel
{
    /**
     * @return array
     */
    public function fake(Generator &$faker)
    {
        // Test example
        $status = MetaStatuses::get('name');

        return [
            'parent'          => 0,
            'locale_id'       => 0,
            'module_id'       => 0,
            'slug'            => $faker->slug(),
            'creator_id'      => 0,
            'item_id'         => 0,
            'title'           => $faker->realText(),
            'sort'            => mt_rand(1, 1000),
            'url'             => '',
            'meta'            => [],
            'meta_sitemap'    => [],
            'status'          => MetaStatuses::Publish->name, // $status[array_rand($status)],
            'meta_type'       => '',
            'page_type'       => 'page',
            'in_sitemap'      => true,
            'use_url_pattern' => false,
            'created_by_id'   => 0,
            'publish_at'      => $faker->dateTimeBetween('-1 year', 'now', 'Asia/Omsk')->format('Y-m-d H:i:s'),
        ];
    }
}
