<?php

namespace AvegaCms\Utils;

use AvegaCms\Models\Admin\LocalesModel;
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
}