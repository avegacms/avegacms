<?php

declare(strict_types = 1);

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
     * @return void
     * @throws ReflectionException
     */
    public function generate(): void
    {
        $this->moduleName = 'Content';
        $this->setGroup(
            list: (new MetaDataSiteMapModel())->getContentSitemap('Pages', CmsModule::meta('content')['id'])
        );
    }
}
