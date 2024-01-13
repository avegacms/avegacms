<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\ModulesModel;

class Seo extends BaseController
{
    public function sitemap()
    {
        dd(
            model(ModulesModel::class)->getModulesSiteMapSchema()
        );
    }

    /**
     * @return ResponseInterface
     */
    public function robots(): ResponseInterface
    {
        if ( ! file_exists(FCPATH . 'robots.txt') || empty($robotsData = file_get_contents('./robots.txt', true))) {
            $robotsData = 'User-agent: *' . PHP_EOL . 'Disallow:';
        }
        return response()->setBody($robotsData)->setStatusCode(200);
    }
}
