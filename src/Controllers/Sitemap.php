<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\MetaDataSiteMapModel;
use AvegaCms\Utilities\SeoUtils;
use ReflectionException;

class Sitemap extends BaseController
{
    /**
     * @param  string  $pointer
     * @return void
     * @throws ReflectionException
     */
    public static function run(string $pointer = ''): void
    {
        $MDSMM = model(MetaDataSiteMapModel::class);

        $sitemap = [
            'content'         => [],
            'content.pages'   => $MDSMM->getContentSitemap('pages'),
            'content.rubrics' => $MDSMM->getContentSitemap('rubrics'),
            'content.posts'   => $MDSMM->getContentSitemap('posts')
        ];

        if (empty($pointer)) {
            foreach ($sitemap as $key => $data) {
                SeoUtils::sitemap($key, $data);
            }
        } elseif (array_key_exists($pointer, $sitemap)) {
            SeoUtils::sitemap($pointer, $sitemap[$pointer]);
        }
    }
}
