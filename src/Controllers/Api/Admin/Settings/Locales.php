<?php

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Models\Admin\LocalesModel;
use CodeIgniter\HTTP\ResponseInterface;


class Locales extends AvegaCmsAPI
{
    protected LocalesModel $LM;

    public function __construct()
    {
        parent::__construct();
        $this->LM = model(LocalesModel::class);
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $locales = $this->LM->filter($this->request->getGet() ?? [])->pagination();
        
        return $this->cmsRespond($locales['list'], $locales['pagination']);
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        if (($data = $this->LM->find($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->respond(['data' => $data]);
    }
}