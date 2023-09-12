<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class AvegaCmsFrontendController extends BaseController
{
    protected array $metaData    = [];
    protected array $breadCrumbs = [];

    public function __construct()
    {
        helper(['avegacms']);
    }

    /**
     * @param  array  $data
     * @param  string  $view
     * @param  array  $options
     * @return string
     */
    public function render(array $data, string $view = '', array $options = []): string
    {
        return view('template/foundation_view', $data);
    }

    /**
     * @return ResponseInterface
     */
    public function error404(): ResponseInterface
    {
        return $this->response->setStatusCode(404)->setBody($this->render([], 'main/pages/page_404'));
    }
}
