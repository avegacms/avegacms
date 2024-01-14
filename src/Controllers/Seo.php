<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Utilities\Cms;
use ReflectionException;

class Seo extends BaseController
{
    /**
     * @return ResponseInterface|void
     * @throws ReflectionException
     */
    public function sitemap()
    {
        if ( ! Cms::settings('core.seo.useSitemap') || ! file_exists(FCPATH . 'uploads/sitemaps/sitemap.xml')) {
            return response()->setStatusCode(404);
        }
        return response()->setBody(file_get_contents('./uploads/sitemaps/sitemap.xml'))->setStatusCode(200);
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function robots(): ResponseInterface
    {
        if ( ! Cms::settings('core.seo.useRobotsTxt')) {
            return response()->setStatusCode(404);
        }
        if ( ! file_exists(FCPATH . 'robots.txt') || empty($robotsData = file_get_contents('./robots.txt', true))) {
            $robotsData = 'User-agent: *' . PHP_EOL . 'Disallow:';
        }
        return response()->setBody($robotsData)->setStatusCode(200);
    }
}
