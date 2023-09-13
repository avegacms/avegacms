<?php

declare(strict_types=1);

namespace AvegaCms\Entities;

use AvegaCms\Models\Admin\{MetaDataModel, LocalesModel};
use AvegaCms\Enums\MetaDataTypes;

class MetaDataEntity extends AvegaCmsEntity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected $casts   = [
        'id'            => 'integer',
        'post_id'       => 'integer',
        'rubric_id'     => 'integer',
        'parent'        => 'integer',
        'locale_id'     => 'integer',
        'module_id'     => 'integer',
        'slug'          => 'string',
        'creator_id'    => 'integer',
        'item_id'       => 'integer',
        'title'         => 'string',
        'sort'          => 'integer',
        'url'           => 'string',
        'meta'          => 'json-array',
        'extra_data'    => 'json-array',
        'status'        => 'string',
        'meta_type'     => 'string',
        'in_sitemap'    => 'integer',
        'rubrics'       => 'array',
        'created_by_id' => 'integer',
        'updated_by_id' => 'integer',
        'publish_at'    => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        helper(['avegacms']);
    }

    /**
     * @param  string  $url
     * @return $this
     */
    public function setUrl(string $url): AvegaCmsEntity
    {
        helper(['url']);

        $settings = settings('core.env');
        $locales = array_column(model(LocalesModel::class)->getLocalesList(), 'slug', 'id');

        $url = empty($url) ? mb_url_title(strtolower($this->rawData['title'])) : $url;

        if ($settings['useMultiLocales']) {
            $url = $locales[$this->rawData['locale_id']] . '/' . $url;
        }

        $this->attributes['url'] = match ($this->rawData['meta_type']) {
            MetaDataTypes::Main->value => $settings['useMultiLocales'] ? $locales[$this->rawData['locale_id']] : '/',
            MetaDataTypes::Page->value => model(MetaDataModel::class)->getParentPageUrl($this->rawData['parent']) . $url,
            default                    => $url
        };

        return $this;
    }

    /**
     * @param  string  $meta
     * @return $this
     */
    public function setMeta(string $meta): AvegaCmsEntity
    {
        $meta = json_decode($meta, true);

        $meta['title'] = empty($meta['title']) ? $this->attributes['title'] : $meta['title'];
        $meta['keywords'] = ! empty($meta['keywords']) ? $meta['keywords'] : '';
        $meta['description'] = ! empty($meta['description']) ? $meta['description'] : '';

        $meta['breadcrumb'] = ! empty($meta['breadcrumb']) ? $meta['breadcrumb'] : '';

        $meta['og:title'] = empty($meta['og:title']) ? $this->attributes['title'] : $meta['og:title'];
        $meta['og:type'] = empty($meta['og:type']) ? 'website' : $meta['og:type'];
        $meta['og:url'] = empty($meta['og:url']) ? $this->attributes['url'] : $meta['og:url'];

        $meta['og:image'] = ! empty($meta['og:image']) ? $meta['og:image'] : '';

        $this->attributes['meta'] = json_encode($meta);

        return $this;
    }

    /**
     * @return array
     */
    public function metaRender(): array
    {
        $meta = $this->meta;

        unset($meta['breadcrumb']);

        $meta['title'] = esc($meta['title']);
        $meta['keywords'] = esc($meta['keywords']);
        $meta['description'] = esc($meta['description']);

        $meta['og:title'] = esc($meta['og:title']);
        $meta['og:type'] = esc($meta['og:type']);
        $meta['og:url'] = base_url($meta['og:url']);
        $meta['og:image'] = '';

        return $meta;
    }

    /**
     * @param  array  $parentBreadCrumbs
     * @return array
     */
    public function breadCrumbs(array $parentBreadCrumbs = []): array
    {
        $breadCrumbs[] = [
            'url'   => '',
            'title' => esc(! empty($this->meta->breadcrumb) ? $this->meta->breadcrumb : $this->title)
        ];

        if ( ! empty($parentBreadCrumbs)) {
            foreach ($parentBreadCrumbs as $crumb) {
                $breadCrumbs[] = [
                    'url'   => base_url($crumb->url),
                    'title' => esc(! empty($crumb->meta->breadcrumb) ? $crumb->meta->breadcrumb : $crumb->title)
                ];
            }
        }

        return array_reverse($breadCrumbs);
    }

    /**
     * @return array
     */
    public function getRubrics(): array
    {
        return array_map(function ($k) {
            return intval($k);
        }, unserialize($this->attributes['rubrics']));
    }
}
