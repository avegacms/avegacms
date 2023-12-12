<?php

namespace AvegaCms\Utilities;

use AvegaCms\Models\Admin\{LocalesModel, MetaDataModel};
use RuntimeException;

class SeoUtils
{
    /**
     * @param  int  $id
     * @return array
     */
    public static function Locales(int $id = 0): array
    {
        $locales = array_column(model(LocalesModel::class)->getLocalesList(), null, 'id');

        if ($id > 0 && ! isset($locales[$id])) {
            throw new RuntimeException('Undefined locale');
        }
        return ($id > 0) ? $locales[$id] : $locales;
    }

    /**
     * @param  int  $id
     * @return array
     */
    public static function LocaleData(int $id): array
    {
        return self::Locales($id)['extra'];
    }

    /**
     * @param  int  $id
     * @return array
     */
    public static function mainPages(int $id = 0): array
    {
        $pages = array_column(model(MetaDataModel::class)->mainPages(), 'id', 'locale');

        if ($id > 0 && ! isset($pages[$id])) {
            throw new RuntimeException('Undefined main page');
        }
        return ($id > 0) ? $pages[$id] : $pages;
    }

    /**
     * @param  int  $locale
     * @param  string|null  $key
     * @param  string|null  $value
     * @return array
     */
    public static function rubricsList(int $locale = 0, ?string $key = null, ?string $value = null): array
    {
        $rubrics = model(MetaDataModel::class)->getRubrics();

        if ($locale > 0) {
            $rubrics = array_filter(array_map(
                callback: function ($item) use ($locale) {
                    return $item['locale_id'] === $locale ? $item : null;
                }, array: $rubrics
            ));
        }

        if (empty($rubrics)) {
            throw new RuntimeException('Undefined rubrics');
        }

        if ( ! is_null($key) || ! is_null($value)) {
            $rubrics = array_column($rubrics, $value, $key);
        }

        return $rubrics;
    }
}