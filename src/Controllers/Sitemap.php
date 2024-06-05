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

        $list = ['Pages', 'Rubrics', 'Posts'];

        if (is_null($pointer)) {
            $this->moduleName = 'Content';
            $this->setModule(group: $list);
            foreach ($list as $item) {
                $this->setModule(group: $item, list: $MDSM->getContentSitemap($item));
            }
        } elseif (in_array($pointer, $list)) {
            $this->setModule(group: $pointer, list: $MDSM->getContentSitemap($pointer));
        }
    }
}
