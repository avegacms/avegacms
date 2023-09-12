<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

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

    public function viewRender(array $data, string $view = '', array $options = []): string
    {
    }
}
