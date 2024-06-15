<?php

declare(strict_types = 1);

namespace AvegaCms\Models\Admin;

use AvegaCms\Enums\{MetaStatuses, MetaDataTypes, SitemapChangefreqs};
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
     * @param  int  $moduleId
     * @return array
     */
    public function getContentSitemap(string $type, int $moduleId = 0): array
    {
        $this->builder()->select(
            [
                'metadata.id',
                'metadata.parent',
                'metadata.locale_id',
                'metadata.module_id',
                'metadata.use_url_pattern',
                'metadata.meta_sitemap',
                'metadata.publish_at AS lastmod'
            ]
        )->groupStart()
            ->where(['metadata.status' => MetaStatuses::Publish->name])
            ->orGroupStart()
            ->where(
                [
                    'metadata.status'        => MetaStatuses::Future->name,
                    'metadata.publish_at <=' => date('Y-m-d H:i:s')
                ]
            )->groupEnd()
            ->groupEnd()
            ->where(
                [
                    'metadata.in_sitemap' => 1,
                    'metadata.module_id'  => $moduleId
                ]
            )->orderBy('metadata.publish_at', 'DESC');


        match ($type) {
            'Pages'   => $this->builder()->whereIn('metadata.meta_type', [
                MetaDataTypes::Main->name,
                MetaDataTypes::Page->name
            ]),
            'Rubrics' => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Rubric->name]),
            'Posts'   => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Post->name]),
            'Module'  => $this->builder()->where(['metadata.meta_type' => MetaDataTypes::Module->name]),
        };

        if ($type === 'Posts') {
            $this->builder()
                ->select(['CONCAT(TRIM(TRAILING "/" FROM CONCAT_WS("/", m2.url, metadata.url)), "_", metadata.id) AS url'])
                ->join('metadata AS m2', 'm2.id = metadata.parent');
        } else {
            $this->builder()->select(['metadata.url']);
        }

        if ( ! empty($list = $this->findAll())) {
            foreach ($list as $item) {
                $item->changefreq = $item->meta_sitemap['changefreq'] ?? SitemapChangefreqs::Monthly->value;
                $item->priority   = $item->meta_sitemap['priority'] ?? 50;
                unset($item->meta_sitemap);
            }
        }

        return $list;
    }
}
