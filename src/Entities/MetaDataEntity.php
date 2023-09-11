<?php

namespace AvegaCms\Entities;

use AvegaCms\Models\Admin\MetaDataModel;
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

    /**
     * @param  string  $url
     * @return $this
     */
    public function setUrl(string $url): AvegaCmsEntity
    {
        helper(['url']);

        $url = empty($url) ? mb_url_title(strtolower($this->rawData['title'])) : $url;

        if ($this->rawData['meta_type'] === MetaDataTypes::Page->value) {
            $url = model(MetaDataModel::class)->getParentPageUrl($this->rawData['parent']) . '/' . $url;
        }

        $this->attributes['url'] = $url;

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

        // TODO Добавить с Locale og:image
        $meta['og:image'] = ! empty($meta['og:image']) ? $meta['og:image'] : base_url('uploads/open_graph.png');

        $this->attributes['meta'] = json_encode($meta);

        return $this;
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
