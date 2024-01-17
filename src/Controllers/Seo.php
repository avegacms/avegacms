<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Enums\MetaChangefreq;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Utilities\Cms;
use ReflectionException;
use AvegaCms\Models\Admin\ModulesModel;

class Seo extends BaseController
{
    public function __construct()
    {
        helper(['filesystem']);
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function sitemap(): ResponseInterface
    {
        if ( ! Cms::settings('core.seo.useSitemap')) {
            (new AvegaCmsFrontendController())->error404();
        }

        if ( ! file_exists(FCPATH . 'uploads/sitemaps/sitemap.xml') ||
            empty($sitemap = file_get_contents('./uploads/sitemaps/sitemap.xml', true))) {
            $sitemap = ['isSiteMap' => true];
            $list    = model(ModulesModel::class)->getModulesSiteMapSchema();
            $date    = date('Y-m-d');

            foreach ($list as $module => $item) {
                $sitemap['links'][] = [
                    'url'        => base_url('/uploads/sitemaps/' . ucfirst($module) . '.xml'),
                    'priority'   => 70,
                    'changefreq' => strtolower(MetaChangefreq::Hourly->value),
                    'date'       => $date
                ];
            }

            if ( ! write_file('./uploads/sitemaps/sitemap.xml',
                ($sitemap = view('template/seo/sitemap.php', $sitemap, ['debug' => false])))) {
                log_message('error', 'Unable to write the sitemaps.xml file');
            }
        }
        return response()->setXML($sitemap)->setStatusCode(200);
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function robots(): ResponseInterface
    {
        if ( ! Cms::settings('core.seo.useRobotsTxt')) {
            (new AvegaCmsFrontendController())->error404();
        }

        if ( ! file_exists(FCPATH . 'robots.txt') ||
            empty($robots = file_get_contents('./robots.txt', true))) {
            if ( ! write_file('./robots.txt', ($robots = view('template/seo/robots.php', [], ['debug' => false])))) {
                log_message('error', 'Unable to write the robots.txt file');
            }
        }

        return response()->setBody($robots)->setStatusCode(200);
    }
}
