<?php

declare(strict_types=1);

namespace AvegaCms\Entities;

use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Utils\{Cms, SeoUtils};
use Config\Services;
use AvegaCms\Entities\Seo\{BreadCrumbsEntity, MetaEntity, OpenGraphEntity};
use ReflectionException;


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

        $this->attributes['slug'] = strtolower(mb_substr($slug, 0, 63));

        return $this;
    }


    /**
     * @param  string  $url
     * @return $this
     * @throws ReflectionException
     */
    public function setUrl(string $url): MetaDataEntity
    {
        $url = empty($url) ? mb_url_title(strtolower($this->rawData['title'])) : $url;

        $this->attributes['url'] = match ($this->rawData['meta_type']) {
            MetaDataTypes::Main->value => Cms::settings('core.env.useMultiLocales') ? SeoUtils::Locales($this->rawData['locale_id'])['slug'] : '/',
            MetaDataTypes::Page->value => model(MetaDataModel::class)->getParentPageUrl($this->rawData['parent']) . $url,
            default                    => strtolower($url)
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

        $data['title']       = $meta['title'] ?? $this->rawData['title'];
        $data['keywords']    = $meta['keywords'] ?? '';
        $data['description'] = $meta['description'] ?? '';

        $data['breadcrumb'] = $meta['breadcrumb'] ?? '';

        $data['og:title'] = $meta['og:title'] ?? $this->rawData['title'];
        $data['og:type']  = $meta['og:type'] ?? 'website';
        $data['og:url']   = $meta['og:url'] ?? $this->attributes['url'];

        $data['og:image'] = $meta['og:image'] ?? '';

        $this->attributes['meta'] = json_encode($data);

        return $this;
    }

    /**
     * @return MetaEntity
     * @throws ReflectionException
     */
    public function metaRender(): MetaEntity
    {
        $page = $this->meta;

        unset($page['breadcrumb']);

        $locales = SeoUtils::Locales();
        $data    = SeoUtils::LocaleData($this->locale_id);

        $meta['title']       = esc($page['title']);
        $meta['keywords']    = esc($page['keywords']);
        $meta['description'] = esc($page['description']);

        $meta['lang'] = $locales[$this->locale_id]['locale'];

        $meta['openGraph'] = (new OpenGraphEntity(
            [
                'locale'   => $meta['lang'],
                'siteName' => esc($data['app_name']),
                'title'    => esc($page['og:title']),
                'type'     => esc($page['og:type']),
                'url'      => esc($page['og:url']),
                'image'    => empty($page['og:image']) ? $data['og:image'] : base_url('uploads/content/' . $page['og:image'])
            ]
        ));

        if ($meta['useMultiLocales'] = Cms::settings('core.env.useMultiLocales')) {
            foreach ($locales as $locale) {
                $meta['alternate'][] = [
                    'hreflang' => ($this->locale_id === $locale['id']) ? 'x-default' : $locale['slug'],
                    'href'     => base_url($locale['slug']),
                ];
            }
        }

        $meta['canonical'] = base_url(Services::request()->uri->getRoutePath());
        $meta['robots']    = ($this->in_sitemap === 1) ? 'index, follow' : 'noindex, nofollow';

        return (new MetaEntity($meta));
    }

    /**
     * @param  string  $type
     * @param  array  $parentBreadCrumbs
     * @return BreadCrumbsEntity[]
     */
    public function breadCrumbs(string $type, array $parentBreadCrumbs = []): array
    {
        $breadCrumbs = [];

        if ($type !== MetaDataTypes::Main->value) {
            $breadCrumbs[] = [
                'url'    => '',
                'title'  => esc(! empty($this->meta->breadcrumb) ? $this->meta->breadcrumb : $this->title),
                'active' => true
            ];
        }

        if ( ! empty($parentBreadCrumbs)) {
            foreach ($parentBreadCrumbs as $crumb) {
                $breadCrumbs[] = [
                    'url'    => base_url($crumb->url),
                    'title'  => esc(! empty($crumb->meta->breadcrumb) ? $crumb->meta->breadcrumb : $crumb->title),
                    'active' => false
                ];
            }
        }

        if ( ! empty($locale = SeoUtils::Locales($this->locale_id))) {
            $breadCrumbs[] = [
                'url'    => base_url(Cms::settings('core.env.useMultiLocales') ? $locale['slug'] : ''),
                'title'  => esc($locale['home']),
                'active' => false
            ];
        }

        return array_map(function ($item) {
            return (new BreadCrumbsEntity($item));
        }, array_reverse($breadCrumbs));
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
