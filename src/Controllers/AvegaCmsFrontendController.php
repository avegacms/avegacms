<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

;

class AvegaCmsFrontendController extends BaseController
{
    protected array $metaData    = [];
    protected array $breadCrumbs = [];

    private readonly array $specialVars;

    public function __construct()
    {
        helper(['avegacms']);
        $this->specialVars = ['meta', 'breadcrumbs'];
    }


    public function render(array $data, string $view = '', array $options = []): string
    {
        if ( ! empty($arr = array_flip(array_intersect_key(array_flip($this->specialVars), $data)))) {
            throw new RuntimeException('Attempt to overwrite system variables: ' . implode(',', $arr));
        }

        $data['meta'] = $this->metaData;
        $data['breadcrumbs'] = $this->breadCrumbs;

        $data['template'] = '';

        return view('template/foundation_view', $data, $options);
    }

    /**
     * @return ResponseInterface
     */
    public function error404(): ResponseInterface
    {
        return $this->response->setStatusCode(404)->setBody($this->render([], 'main/pages/page_404'));
    }
}
