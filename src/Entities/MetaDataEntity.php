<?php

declare(strict_types = 1);

namespace AvegaCms\Entities;

use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Enums\{MetaDataTypes, MetaChangefreq};
use AvegaCms\Utilities\{Cms, SeoUtils};
use Config\Services;
use AvegaCms\Entities\Seo\{BreadCrumbsEntity, MetaEntity, OpenGraphEntity, SiteMapEntity};
use ReflectionException;

/**
 * @property int|null $id
 * @property int|null $parent
 * @property int|null $rubricId
 * @property int|null $moduleId
 * @property array|object|null $meta
 * @property int|null $localeId
 * @property int|null $inSitemap
 * @property int|null $useUrlPattern
 * @property string|null $title
 * @property string|null $url
 * @property string|null $slug
 * @property string|null $metaType
 * @property array $breadCrumbs
 * @property array|null $dictionary
 * @property int|null $parentCrumbId
 * @property MetaEntity $metaRender
 */
class MetaDataEntity extends AvegaCmsEntity
{
    protected $datamap = [
        'postId'        => 'post_id',
        'rubricId'      => 'rubric_id',
        'localeId'      => 'locale_id',
        'moduleId'      => 'module_id',
        'creatorId'     => 'creator_id',
        'itemId'        => 'item_id',
        'extraData'     => 'extra_data',
        'metaType'      => 'meta_type',
        'inSitemap'     => 'in_sitemap',
        'metaSitemap'   => 'meta_sitemap',
        'useUrlPattern' => 'use_url_pattern',
        'createdById'   => 'created_by_id',
        'updatedById'   => 'updated_by_id',
        'publishAt'     => 'publish_at'
    ];
    protected $dates   = ['created_at', 'updated_at', 'publish_at'];
    protected $casts   = [
        'id'              => 'integer',
        'post_id'         => 'integer',
        'rubric_id'       => 'integer',
        'parent'          => 'integer',
        'locale_id'       => 'integer',
        'module_id'       => 'integer',
        'slug'            => 'string',
        'creator_id'      => 'integer',
        'item_id'         => 'integer',
        'title'           => 'string',
        'sort'            => 'integer',
        'url'             => 'string',
        'meta'            => 'json-array',
        'extra_data'      => 'json-array',
        'status'          => 'string',
        'meta_type'       => 'string',
        'in_sitemap'      => 'integer',
        'meta_sitemap'    => 'json-array',
        'use_url_pattern' => 'integer',
        'rubrics'         => 'array',
        'created_by_id'   => 'integer',
        'updated_by_id'   => 'integer',
        'publish_at'      => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Специальный массив-словарь для замены масок на пользовательские значения метаданных
     *
     * @var array|null
     */
    public ?array $dictionary = null;

    /**
     * Параметр для указания кастомного ID родительской записи, от которой будет формироваться breadcrumbs
     * @var int|null
     */
    public ?int $parentCrumbId = null;

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
    }

    /**
     * @return $this
     */
    public function setSlug(): MetaDataEntity
    {
        if (empty($slug = $this->rawData['slug'])) {
            helper(['url']);
            $slug = mb_url_title(strtolower($this->rawData['title']));
        }

        $this->attributes['slug'] = strtolower(mb_substr($slug, 0, 64));

        return $this;
    }

    /**
     * @return $this
     * @throws ReflectionException
     */
    public function setUrl(): MetaDataEntity
    {
        $url = empty($url = $this->attributes['url'] ?? ($this->rawData['url'] ?? '')) ? mb_url_title(strtolower($this->rawData['title'])) : $url;

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

        unset($data);

        return $this;
    }

    /**
     * @return $this
     */
    public function setMetaSitemap(): MetaDataEntity
    {
        $meta = json_decode($this->attributes['meta_sitemap'] ?? '', true);

        $meta['priority']   = $meta['priority'] ?? 50;
        $meta['changefreq'] = $meta['changefreq'] ?? MetaChangefreq::Monthly->value;

        $this->attributes['meta_sitemap'] = json_encode($meta);

        unset($meta);

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return Cms::urlPattern(
            $this->attributes['url'],
            $this->attributes['use_url_pattern'],
            $this->attributes['id'],
            $this->attributes['slug'],
            $this->attributes['locale_id'],
            $this->attributes['parent']
        );
    }

    /**
     * @return MetaEntity
     * @throws ReflectionException
     */
    public function metaRender(): MetaEntity
    {
        $page = $this->meta;

        if ($this->dictionary !== null) {
            $page['title']       = $page['og:title'] = strtr($page['title'], $this->dictionary);
            $page['keywords']    = strtr($page['keywords'], $this->dictionary);
            $page['description'] = strtr($page['description'], $this->dictionary);
        }

        $locales = SeoUtils::Locales();
        $data    = SeoUtils::LocaleData($this->localeId);

        $meta['title']       = esc($page['title']);
        $meta['keywords']    = esc($page['keywords']);
        $meta['description'] = esc($page['description']);

        $meta['slug'] = $this->slug;
        $meta['lang'] = $locales[$this->localeId]['locale'];
        $meta['url']  = $this->url;

        $meta['openGraph'] = (new OpenGraphEntity(
            data: [
                'locale'   => $meta['lang'],
                'siteName' => esc($data['app_name']),
                'title'    => esc($page['og:title']),
                'type'     => esc($page['og:type']),
                'url'      => $meta['url'],
                'image'    => empty($page['og:image']) ? $data['og:image'] : base_url('uploads/content/' . $page['og:image'])
            ]
        ));

        if ($meta['useMultiLocales'] = Cms::settings('core.env.useMultiLocales')) {
            foreach ($locales as $locale) {
                $meta['alternate'][] = [
                    'hreflang' => ($this->localeId === $locale['id']) ? 'x-default' : $locale['slug'],
                    'href'     => base_url($locale['slug']),
                ];
            }
        }

        $meta['canonical'] = base_url(Services::request()->getUri()->getRoutePath());
        $meta['robots']    = ($this->inSitemap === 1) ? 'index, follow' : 'noindex, nofollow';

        return (new MetaEntity($meta));
    }

    /**
     * @return SiteMapEntity
     */
    public function siteMapData(): SiteMapEntity
    {
        return (new SiteMapEntity(json_decode($this->attributes['meta_sitemap'], true)));
    }

    /**
     * @param  string  $type
     * @param  array  $parentBreadCrumbs
     * @return BreadCrumbsEntity[]
     * @throws ReflectionException
     */
    public function breadCrumbs(string $type, array $parentBreadCrumbs = []): array
    {
        $breadCrumbs = [];

        if ($type !== MetaDataTypes::Main->value) {
            $breadCrumbs[] = [
                'url'    => '',
                'title'  => strtr(
                    esc(! empty($this->meta['breadcrumb']) ? $this->meta['breadcrumb'] : $this->title),
                    $this->dictionary ?? []
                ),
                'active' => true
            ];
        }

        if ( ! empty($parentBreadCrumbs)) {
            foreach ($parentBreadCrumbs as $crumb) {
                if ($crumb->meta_type !== MetaDataTypes::Main->value) {
                    $breadCrumbs[] = [
                        'url'    => $crumb->url,
                        'title'  => esc(! empty($crumb->meta->breadcrumb) ? $crumb->meta->breadcrumb : $crumb->title),
                        'active' => false
                    ];
                }
            }
        }

        if ( ! empty($locale = SeoUtils::Locales($this->localeId))) {
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
