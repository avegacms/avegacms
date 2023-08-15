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

    public function index(): ResponseInterface
    {
        $filter = $this->request->getGet() ?? [];

        return $this->cmsRespond(
            $this->LM->filter($filter)->paginate($this->LM->limit),
            [
                'pagination' => [
                    'current_page' => (int) ($filter['page'] ?? 1),
                    'per_page'     => $this->LM->limit,
                    'total'        => $this->LM->pager->getTotal()
                ]
            ]
        );
    }

    public function show($id = null): ResponseInterface
    {
        if (($data = $this->LM->find($id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->respond(['data' => $data]);
    }
}