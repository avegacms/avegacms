<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\{MetaStatuses, MetaDataTypes};
use AvegaCms\Models\AvegaCmsModel;

class MetaDataSiteMapModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'metadata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';

    protected array $casts = [
        'id'              => 'int',
        'parent'          => 'int',
        'locale_id'       => 'int',
        'module_id'       => 'int',
        'meta_sitemap'    => '?json-array',
        'use_url_pattern' => 'int-bool',
        'publish_at'      => 'cmsdatetime'
    ];

    /**
     * @param  string  $type
     * @return array
     */
    public function getContentSitemap(string $type): array
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.url',
                'metadata.use_url_pattern',
                'metadata.meta_sitemap',
                'metadata.publish_at AS lastmod'
            ]
        )->groupStart()
            ->where(['metadata.status' => MetaStatuses::Publish->value])
            ->orGroupStart()
            ->where(
                [
                    'metadata.status'        => MetaStatuses::Future->value,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s')
                ]
            )->groupEnd()
            ->groupEnd()
            ->where(
                [
                    'metadata.in_sitemap' => 1,
                    'metadata.module_id'  => 0
                ]
            )->orderBy('metadata.publish_at', 'ASC');


        match ($type) {
            'Pages'   => $this->builder()->whereIn('metadata.meta_type', [
                MetaDataTypes::Main->value,
                MetaDataTypes::Page->value
            ]),
            'Rubrics' => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Rubric->value]),
            'Posts'   => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Post->value])
        };

        return $this->findAll();
    }
}
