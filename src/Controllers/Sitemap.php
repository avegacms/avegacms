<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\MetaDataSiteMapModel;
use AvegaCms\Traits\AvegaCmsSitemapTrait;
use AvegaCms\Utilities\CmsModule;
use CodeIgniter\Controller;
use ReflectionException;

class Sitemap extends Controller
{
    use AvegaCmsSitemapTrait;

    /**
     * @throws ReflectionException
     */
    public function generate(): void
    {
        $this->moduleName = 'Pages';
        $this->setGroup(
            list: (new MetaDataSiteMapModel())->getContentSitemap('Pages', CmsModule::meta('pages')['id'])
        );
    }
}
