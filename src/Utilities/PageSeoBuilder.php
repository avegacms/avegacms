<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\Services;
use AvegaCms\Enums\MetaDataTypes;
use ReflectionException;

class PageSeoBuilder
{
    /**
     * Объект с данными из таблицы metadata
     */
    public object $data;

    /**
     * Специальный массив-словарь для замены масок на пользовательские значения метаданных
     */
    public ?array $dictionary;

    public function __construct(object $data)
    {
        $this->dictionary = $data->MetaDictionary ?? null;
        unset($data->MetaDictionary);

        $this->data = $data;
    }

    /**
     * @throws ReflectionException
     */
    public function meta(): object
    {
        $locales = SeoUtils::Locales();
        $data    = SeoUtils::LocaleData($this->data->locale_id);

        $meta['title']       = htmlspecialchars_decode(esc($this->data->meta['title']));
        $meta['keywords']    = htmlspecialchars_decode(esc($this->data->meta['keywords']));
        $meta['description'] = htmlspecialchars_decode(esc($this->data->meta['description']));

        if ($this->dictionary !== null) {
            $meta['title']       = esc(strtr($this->data->meta['title'], $this->dictionary));
            $meta['keywords']    = esc(strtr($this->data->meta['keywords'], $this->dictionary));
            $meta['description'] = esc(strtr($this->data->meta['description'], $this->dictionary));
        }

        $meta['slug'] = $this->data->slug;
        $meta['lang'] = $locales[$this->data->locale_id]['locale'];
        $meta['url']  = base_url(
            strtolower(
                $this->data->use_url_pattern ?
                    str_ireplace(
                        ['{id}', '{slug}', '{locale_id}', '{parent}'],
                        [$this->data->id, $this->data->slug, $this->data->locale_id, $this->data->parent],
                        $this->data->url
                    ) :
                    $this->data->url
            )
        );

        $meta['open_graph'] = (object) [
            'locale'    => $meta['lang'],
            'site_name' => esc($data['app_name']),
            'title'     => $meta['title'],
            'type'      => esc($this->data->meta['og:type']),
            'url'       => $meta['url'],
            'image'     => $this->data->meta['og:image'], // TODO подумать как лучше получать картинку для OG
        ];

        $meta['preview']   = $this->data->preview ?? null;
        $meta['page_type'] = $this->data->meta_type;

        if ($meta['use_multi_locales'] = Cms::settings('core.env.useMultiLocales')) {
            foreach ($locales as $locale) {
                $meta['alternate'][] = [
                    'hreflang' => ($this->data->locale_id === $locale['id']) ? 'x-default' : $locale['slug'],
                    'href'     => base_url($locale['slug']),
                ];
            }
        }

        $meta['canonical'] = base_url(Services::request()->getUri()->getRoutePath());
        $meta['robots']    = $this->data->in_sitemap ? 'index, follow' : 'noindex, nofollow';

        return (object) $meta;
    }

    /**
     * @throws ReflectionException
     */
    public function breadCrumbs(string $type, ?array $parentBreadCrumbs = null): array
    {
        $breadCrumbs = [];

        if ($type !== MetaDataTypes::Main->name) {
            $breadCrumbs[] = [
                'url'   => '',
                'title' => strtr(
                    htmlspecialchars_decode(
                        esc(! empty($this->data->meta['breadcrumb']) ? $this->data->meta['breadcrumb'] : $this->data->title)
                    ),
                    $this->dictionary ?? []
                ),
                'active' => true,
            ];
        }

        if (! empty($parentBreadCrumbs)) {
            foreach ($parentBreadCrumbs as $crumb) {
                if ($crumb->meta_type !== MetaDataTypes::Main->name) {
                    $breadCrumbs[] = [
                        'url'   => $crumb->url,
                        'title' => htmlspecialchars_decode(
                            esc(! empty($crumb->meta->breadcrumb) ? $crumb->meta->breadcrumb : $crumb->title)
                        ),
                        'active' => false,
                    ];
                }
            }
        }

        if (! empty($locale = SeoUtils::Locales($this->data->locale_id))) {
            $breadCrumbs[] = [
                'url'   => base_url(Cms::settings('core.env.useMultiLocales') ? $locale['slug'] : ''),
                'title' => htmlspecialchars_decode(
                    esc($locale['home'])
                ),
                'active' => false,
            ];
        }

        return array_map(static fn ($item) => $item, array_reverse($breadCrumbs));
    }
}
