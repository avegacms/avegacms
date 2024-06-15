<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\MetaDataSiteMapModel;
use AvegaCms\Traits\AvegaCmsSitemapTrait;
use CodeIgniter\Controller;
use Exception;

class Sitemap extends Controller
{
    use AvegaCmsSitemapTrait;

    /**
     * @param  string|null  $pointer
     * @return void
     * @throws Exception
     */
    public function generate(?string $pointer = null): void
    {
        $MDSM = new MetaDataSiteMapModel();

        $this->moduleName = 'Content';
        $list             = ['Pages', 'Rubrics', 'Posts'];

        if (is_null($pointer)) {
            $this->setModule(group: $list);
            foreach ($list as $item) {
                $this->setGroup(group: $item, list: $MDSM->getContentSitemap($item));
            }
        } elseif (in_array($pointer, $list)) {
            $this->setGroup(group: $pointer, list: $MDSM->getContentSitemap($pointer));
        }
    }
}
