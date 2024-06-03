<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\MetaDataSiteMapModel;
use AvegaCms\Traits\CmsSitemapTrait;
use CodeIgniter\Controller;
use ReflectionException;
use Exception;

class Sitemap extends Controller
{
    use CmsSitemapTrait;

    /**
     * @param  string  $pointer
     * @return void
     * @throws ReflectionException|Exception
     */
    public function run(string $pointer = ''): void
    {
        if (in_array($pointer, ['pages', 'rubrics', 'posts'])) {
            $this->setModule(group: $pointer, list: (new MetaDataSiteMapModel())->getContentSitemap($pointer));
        }
    }
}
