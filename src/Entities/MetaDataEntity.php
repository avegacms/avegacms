<?php

declare(strict_types=1);

namespace AvegaCms\Entities;

use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Utils\SeoUtils;
use Config\Services;

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
     * @param  string  $slug
     * @return $this
     */
    public function setSlug(string $slug): MetaDataEntity
    {
        if (empty($slug)) {
            helper(['url']);
            $slug = mb_url_title(strtolower($this->rawData['title']));
        }

        $this->attributes['slug'] = mb_substr($slug, 0, 63);

        return $this;
    }


    /**
     * @param  string  $url
     * @return $this
     */
    public function setUrl(string $url): MetaDataEntity
    {
        $url = empty($url) ? mb_url_title(strtolower($this->rawData['title'])) : $url;

        $this->attributes['url'] = match ($this->rawData['meta_type']) {
            MetaDataTypes::Main->value => settings('core.env.useMultiLocales') ? SeoUtils::Locales($this->rawData['locale_id'])['slug'] : '/',
            MetaDataTypes::Page->value => model(MetaDataModel::class)->getParentPageUrl($this->rawData['parent']) . $url,
            default                    => $url
        };

        return $this;
    }

    /**
     * @param  string  $meta
     * @return $this
     */
    public function setMeta(string $meta): MetaDataEntity
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

        $locales = SeoUtils::Locales();
        $data = SeoUtils::LocaleData($this->locale_id);

        $meta['title'] = esc($meta['title']);
        $meta['keywords'] = esc($meta['keywords']);
        $meta['description'] = esc($meta['description']);

        $meta['lang'] = $meta['og:locale'] = $locales[$this->locale_id]['locale'];

        $meta['og:site_name'] = esc($data['app_name']);
        $meta['og:title'] = esc($meta['og:title']);
        $meta['og:type'] = esc($meta['og:type']);
        $meta['og:url'] = base_url($meta['og:url']);
        $meta['og:image'] = empty($item['og:image']) ? $data['og:image'] : base_url('uploads/content/' . $item['og:image']);

        if ($meta['useMultiLocales'] = settings('core.env.useMultiLocales')) {
            foreach ($locales as $locale) {
                $meta['alternate'][] = [
                    'hreflang' => ($this->locale_id === $locale['id']) ? 'x-default' : $locale['slug'],
                    'href'     => base_url($locale['slug']),
                ];
            }
        }

        $meta['canonical'] = base_url(Services::request()->uri->getRoutePath());
        $meta['robots'] = ($this->in_sitemap === 1) ? 'index, follow' : 'noindex, nofollow';

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

        if ( ! empty($locale = SeoUtils::Locales($this->locale_id))) {
            $breadCrumbs[] = [
                'url'   => base_url(settings('core.env.useMultiLocales') ? $locale['slug'] : ''),
                'title' => esc($locale['home'])
            ];
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
