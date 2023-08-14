<?php

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\AvegaCmsAPI;
use AvegaCms\Models\Admin\LocalesModel;

class Locales extends AvegaCmsAPI
{
    protected LocalesModel $LM;

    public function __construct()
    {
        parent::__construct();

        $this->LM = model(LocalesModel::class);
    }

    public function index()
    {
        return $this->respond(['data' => 'Locales/index']);
    }
}