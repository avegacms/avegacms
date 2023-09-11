<?php

namespace AvegaCms\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
use AvegaCms\Models\Admin\LocalesModel;
use Config\Services;

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

        $locales = model(LocalesModel::class)->getLocalesList();

        $defLocale = array_column($locales, null, 'is_default')[1];
        unset($defLocale['is_default']);

        initClientSession(['client' => ['locale' => $defLocale]]);

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
                $user['client']['locale'] = array_column($locales, null, 'slug')[$segment];
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
