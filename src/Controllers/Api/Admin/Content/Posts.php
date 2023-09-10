<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\MetaDataTypes;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel, PostRubricsModel};
use AvegaCms\Entities\{MetaDataEntity, ContentEntity, PostRubricsEntity};
use ReflectionException;

class Posts extends AvegaCmsAdminAPI
{
    protected ContentModel     $CM;
    protected MetaDataModel    $MDM;
    protected PostRubricsModel $PRM;

    public function __construct()
    {
        parent::__construct();
        $this->CM = model(ContentModel::class);
        $this->MDM = model(MetaDataModel::class);
        $this->PRM = model(PostRubricsModel::class);
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $meta = $this->PRM->selectPosts()
            ->filter($this->request->getGet() ?? [])
            ->pagination();

        return $this->cmsRespond($meta['list'], $meta['pagination']);
    }

    /**
     * Return the properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function show($id = null): ResponseInterface
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        //
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function create(): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        $data['module_id'] = 0;
        $data['meta_type'] = MetaDataTypes::Post->value;
        $data['creator_id'] = $data['created_by_id'] = $this->userData->userId;

        $content['anons'] = $data['anons'];
        $content['content'] = $data['content'];
        $content['extra'] = $data['extra'];

        $rubrics = array_unique($data['rubrics'], SORT_NUMERIC);

        unset($data['anons'], $data['content'], $data['extra'], $data['rubrics']);

        if ( ! $id = $this->MDM->insert((new MetaDataEntity($data)))) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        $content['meta_id'] = $id;

        if ($this->CM->insert((new ContentEntity($content))) === false) {
            return $this->failValidationErrors($this->CM->errors());
        }

        $postRubrics = [];

        foreach ($rubrics as $rubric) {
            $postRubrics[] = (new PostRubricsEntity(
                [
                    'post_id'   => $id,
                    'rubric_id' => $rubric
                ]
            ));
        }

        $this->PRM->insertBatch($postRubrics);

        return $this->cmsRespondCreated($id);
    }

    /**
     * Return the editable properties of a resource object
     *
     * @param $id
     * @return ResponseInterface
     */
    public function edit($id = null): ResponseInterface
    {
        if (($data = $this->MDM->postEdit((int) $id)) === null) {
            return $this->failNotFound();
        }

        $data->rubrics = $this->PRM->where(['post_id' => $id])->findColumn('rubric_id');

        return $this->cmsRespond($data->toArray());
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function update($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->MDM->pageEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['module_id'] = 0;
        unset($data['creator_id']);
        $data['updated_by_id'] = $this->userData->userId;

        $content['anons'] = $data['anons'];
        $content['content'] = $data['content'];
        $content['extra'] = $data['extra'];

        $rubrics = array_unique($data['rubrics'], SORT_NUMERIC);

        unset($data['anons'], $data['content'], $data['extra'], $data['rubrics']);

        if ($this->MDM->save((new MetaDataEntity($data))) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        if ($this->CM->where(['meta_id' => $id])->update(null, (new ContentEntity($content))) === false) {
            return $this->failValidationErrors($this->CM->errors());
        }

        // Очищаем список категорий
        $this->PRM->where(['post_id' => $id])->delete();

        foreach ($rubrics as $rubric) {
            $postRubrics[] = (new PostRubricsEntity(
                [
                    'post_id'   => $id,
                    'rubric_id' => $rubric
                ]
            ));
        }

        $this->PRM->insertBatch($postRubrics);

        return $this->respondNoContent();
    }

    /**
     * Delete the designated resource object from the model
     *
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        //
    }
}
