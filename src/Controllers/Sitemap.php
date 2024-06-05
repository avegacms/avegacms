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
     * @param  string|null  $pointer
     * @return void
     * @throws Exception
     */
    public function generate(?string $pointer = null): void
    {
        $MDSM = new MetaDataSiteMapModel();

        $list = ['pages', 'rubrics', 'posts'];

        if (is_null($pointer)) {
            $this->setModule(group: 'Content', list: $list);
            foreach ($list as $item) {
                $this->setModule(group: 'Content', list: $MDSM->getContentSitemap($item));
            }
        } elseif (in_array($pointer, $list)) {
            $this->setModule(group: $pointer, list: $MDSM->getContentSitemap($pointer));
        }
    }
}
