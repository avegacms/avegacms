<?php

declare(strict_types=1);

namespace AvegaCms\Filters;

use AvegaCms\Config\Services;
use AvegaCms\Utilities\Cms;
use AvegaCms\Utilities\SeoUtils;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class FrontendFilter implements FilterInterface
{
    /**
     * @return RedirectResponse|ResponseInterface|void
     *
     * @throws ReflectionException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $settings = Cms::settings('core.env');

        if ($settings['useFrontend'] === false) {
            return Services::response()->setStatusCode(401);
        }

        $locales      = array_column(SeoUtils::Locales(), null, 'slug');
        $segment      = strtolower($request->getUri()->getSegment(1));
        $excludedUrls = [
            'sitemap.xml',
            'robots.txt',
            'uploads',
            'admin',
            'api',
        ];

        Cms::initClientSession([
            'client' => [
                'locale' => [
                    'id'   => $locales[$settings['defLocale']]['id'],
                    'slug' => $settings['defLocale'],
                ],
            ],
        ]);

        if (! in_array($segment, $excludedUrls, true)) {
            if ($settings['useMultiLocales']) {
                if (empty($segment)) {
                    return redirect()->to('/' . $settings['defLocale'], 301);
                }
                if (! in_array($segment, array_column($locales, 'slug'), true)) {
                    return redirect()->to('/' . $settings['defLocale'] . '/page-not-found', 301);
                }
                $user = session()->get('avegacms');
                if ($user['client']['locale']['slug'] !== $segment) {
                    $user['client']['locale'] = [
                        'id'   => $locales[$segment]['id'],
                        'slug' => $locales[$segment]['slug'],
                    ];
                    session()->set('avegacms', $user);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
