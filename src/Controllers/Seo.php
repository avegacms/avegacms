<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Enums\MetaChangefreq;
use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Utilities\Cms;
use AvegaCms\Utilities\SeoUtils;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Seo extends BaseController
{
    /**
     * @throws ReflectionException
     */
    public function sitemap(): ResponseInterface
    {
        if (! Cms::settings('core.seo.useSitemap')) {
            (new AvegaCmsFrontendController())->error404();
        }

        if (! file_exists(FCPATH . 'uploads/sitemaps/Sitemap.xml')) {
            $sitemap = ['isSiteMap' => true];
            $list    = model(ModulesModel::class)->getModulesSiteMapSchema();
            $date    = date('Y-m-d');

            foreach ($list as $module => $item) {
                $sitemap['links'][] = [
                    'url'        => base_url('/uploads/sitemaps/' . ucfirst($module) . '.xml'),
                    'priority'   => 70,
                    'changefreq' => strtolower(MetaChangefreq::Hourly->value),
                    'date'       => $date,
                ];
            }

            SeoUtils::createSiteMapXml('sitemap', $sitemap);
        }

        return response()->setXML(file_get_contents('./uploads/sitemaps/Sitemap.xml', true))->setStatusCode(200);
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
