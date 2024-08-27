<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Traits\AvegaCmsSitemapTrait;
use AvegaCms\Utilities\Cms;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use ReflectionException;

class Seo extends AvegaCmsController
{
    use AvegaCmsSitemapTrait;

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function sitemap(): ResponseInterface
    {
        if (! Cms::settings('core.seo.useSitemap')) {
            (new AvegaCmsFrontendController())->error404();
        }

        if (! file_exists(FCPATH . 'sitemap.xml')) {
            $this->setModule(group: array_keys((new ModulesModel())->getModulesSiteMapSchema()));
        }

        return response()->setXML(file_get_contents('./sitemap.xml', true))->setStatusCode(200);
    }

    /**
     * @throws ReflectionException
     */
    public function robots(): ResponseInterface
    {
        helper(['filesystem']);

        if (! Cms::settings('core.seo.useRobotsTxt')) {
            (new AvegaCmsFrontendController())->error404();
        }

        if (! file_exists(FCPATH . 'robots.txt')
            || empty($robots = file_get_contents('./robots.txt', true))) {
            if (! write_file('./robots.txt', ($robots = view('template/seo/robots.php', [], ['debug' => false])))) {
                log_message('error', 'Unable to write the robots.txt file');
            }
        }

        return response()->setBody($robots)->setStatusCode(200);
    }
}
