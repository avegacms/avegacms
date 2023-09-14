<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;
use AvegaCms\Utils\SeoUtils;

class FrontendFilter implements FilterInterface
{
    protected array $excludedUrls = [
        'sitemap.xml',
        'robots.txt',
        'uploads',
        'api'
    ];

    /**
     * @param  RequestInterface  $request
     * @param $arguments
     * @return RedirectResponse|ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['avegacms']);

        $settings = settings('core.env');

        if ($settings['useFrontend'] === false) {
            return Services::response()->setStatusCode(404);
        }

        $locales = array_column(SeoUtils::Locales(), null, 'slug');

        initClientSession([
            'client' => [
                'locale' => [
                    'id'   => $locales[$settings['defLocale']]['id'],
                    'slug' => $settings['defLocale']
                ]
            ]
        ]);

        if ($settings['useMultiLocales']) {
            if (empty($segment = strtolower($request->uri->getSegment(1)))) {
                return redirect()->to('/' . $settings['defLocale'], 301);
            }

            if ( ! in_array($segment, $this->excludedUrls, true) &&
                ! in_array($segment, array_column($locales, 'slug'), true)
            ) {
                return redirect()->to('/' . $settings['defLocale'] . '/page-not-found', 301);
            }

            $user = session()->get('avegacms');

            if ($user['client']['locale']['slug'] !== $segment) {
                $user['client']['locale'] = ['id' => $locales[$segment]['id'], 'slug' => $locales[$segment]['slug']];
                session()->set('avegacms', $user);
            }
        }
    }

    /**
     * @param  RequestInterface  $request
     * @param  ResponseInterface  $response
     * @param $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
