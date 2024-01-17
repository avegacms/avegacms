<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\{MetaChangefreq, MetaStatuses, MetaDataTypes};
use AvegaCms\Entities\MetaDataSiteMapEntity;
use AvegaCms\Models\AvegaCmsModel;

class MetaDataSiteMapModel extends AvegaCmsModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'metadata';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = MetaDataSiteMapEntity::class;

    /**
     * @param  string  $type
     * @return array
     */
    public function getContentSitemap(string $type): array
    {
        $this->metaSiteMap();

        $this->builder()->where(
            [
                'metadata.in_sitemap' => 1,
                'metadata.module_id'  => 0
            ]
        );

        match ($type) {
            'pages'   => $this->builder()->whereIn('metadata.meta_type', [
                MetaDataTypes::Main->value,
                MetaDataTypes::Page->value
            ]),
            'rubrics' => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Rubric->value]),
            'posts'   => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Post->value])
        };

        return $this->prepData($this->findAll());
    }

    protected function metaSiteMap(): MetaDataSiteMapModel
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
                'metadata.publish_at'
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
            ->orderBy('metadata.publish_at', 'ASC');

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepData($data): array
    {
        $list = [];
        foreach ($data as $item) {
            $list[] = [
                'url'        => base_url($item->url),
                'priority'   => $item->metaSitemap['priority'] ?? 50,
                'changefreq' => strtolower($item->metaSitemap['changefreq'] ?? MetaChangefreq::Monthly->value),
                'date'       => $item->publishAt->toDateString()
            ];
        }
        return $list;
    }
}
