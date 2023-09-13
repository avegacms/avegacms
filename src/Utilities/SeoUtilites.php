<?php

namespace AvegaCms\Utilities;

use AvegaCms\Models\Admin\LocalesModel;

class SeoUtilites
{
    /**
     * @param  int  $id
     * @return array
     */
    public static function Locales(int $id = 0): array
    {
        $locales = array_column(model(LocalesModel::class)->getLocalesList(), null, 'id');
        return ($id > 0) ? ($locales[$id] ?? []) : $locales;
    }
}