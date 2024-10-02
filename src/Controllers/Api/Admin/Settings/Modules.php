<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Settings;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Models\Admin\PermissionsModel;
use CodeIgniter\HTTP\ResponseInterface;

class Modules extends AvegaCmsAdminAPI
{
    protected ModulesModel $MM;
    protected PermissionsModel $PM;

    public function __construct()
    {
        parent::__construct();
        $this->MM = model(ModulesModel::class);
        $this->PM = model(PermissionsModel::class);
    }

    public function index(): ResponseInterface
    {
        return $this->cmsRespond(
            $this->MM->getModules()
        );
    }

    public function show($id = null): ResponseInterface
    {
        if (($data = $this->MM->forEdit((int) $id)) === null) {
            return $this->failNotFound(lang('Api.errors.noData'));
        }

        return $this->cmsRespond(['module' => $data->toArray(), 'submodules' => $this->MM->getModules($id)]);
    }

    public function delete($id = null): ResponseInterface
    {
        $excludedId  = [1, 2, 3, 4, 5];
        $pluginsSlug = ['content_builder', 'uploader'];

        if (($data = $this->MM->find($id)) === null) {
            return $this->failNotFound();
        }

        if (in_array($id, $excludedId, true) || in_array($data->parent, $excludedId, true) || in_array($data->slug, $pluginsSlug, true)) {
            return $this->cmsRespondFail(lang('Modules.errors.deleteIsDefault'));
        }

        $modulesId = $this->MM->parentsId($id)->findColumn('id');

        if (! $this->MM->parentsId($id)->delete()) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Modules']));
        }

        if (! $this->PM->whereIn('id', $modulesId)->delete()) {
            return $this->cmsRespondFail(lang('Api.errors.delete', ['Permissions']));
        }

        cache()->clean();

        return $this->respondNoContent();
    }
}
