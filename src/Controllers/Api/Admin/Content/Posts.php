<?php

declare(strict_types=1);

namespace AvegaCms\Controllers\Api\Admin\Content;

use AvegaCms\Controllers\Api\Admin\AvegaCmsAdminAPI;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel, PostRubricsModel};
use AvegaCms\Entities\{MetaDataEntity, ContentEntity, PostRubricsEntity};
use AvegaCms\Utils\SeoUtils;
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
     * @return ResponseInterface
     */
    public function new(): ResponseInterface
    {
        return $this->cmsRespond(
            [
                'statuses'  => MetaStatuses::getValues(),
                'defStatus' => MetaStatuses::Draft->value,
                'locales'   => array_column(SeoUtils::Locales(), 'locale_name', 'id'),
                'rubrics'   => $this->MDM->getRubrics()
            ]
        );
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

        $data['module_id'] = $data['parent'] = $data['item_id'] = 0;
        $data['meta_type'] = MetaDataTypes::Post->value;
        $data['creator_id'] = $data['created_by_id'] = $this->userData->userId;

        $content = [
            'anons'   => $data['anons'],
            'content' => $data['content'],
            'extra'   => $data['extra']
        ];

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

        if ($this->MDM->postEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['module_id'] = $data['parent'] = $data['item_id'] = 0;
        $data['updated_by_id'] = $this->userData->userId;

        $content['anons'] = $data['anons'];
        $content['content'] = $data['content'];
        $content['extra'] = $data['extra'];

        $rubrics = array_unique($data['rubrics'], SORT_NUMERIC);

        unset($data['creator_id'], $data['anons'], $data['content'], $data['extra'], $data['rubrics']);

        if ($this->MDM->save((new MetaDataEntity($data))) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        if ($this->CM->where(['meta_id' => $id])->update(null, (new ContentEntity($content))) === false) {
            return $this->failValidationErrors($this->CM->errors());
        }

        $this->_removeRubrics((int) $id);

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

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function patch($id = null): ResponseInterface
    {
        if (empty($data = $this->request->getJSON(true))) {
            return $this->failValidationErrors(lang('Api.errors.noData'));
        }

        if ($this->MDM->postEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        $data['updated_by_id'] = $this->userData->userId;

        if ($this->MDM->save((new MetaDataEntity($data))) === false) {
            return $this->failValidationErrors($this->MDM->errors());
        }

        return $this->respondNoContent();
    }

    /**
     * @param $id
     * @return ResponseInterface
     */
    public function delete($id = null): ResponseInterface
    {
        if ($this->MDM->postEdit((int) $id) === null) {
            return $this->failNotFound();
        }

        if ($this->MDM->delete($id) === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Metadata']));
        }

        if ($this->CM->where(['meta_id' => $id])->delete() === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['Content']));
        }

        if ($this->_removeRubrics((int) $id) === false) {
            return $this->failValidationErrors(lang('Api.errors.delete', ['PostRubrics']));
        }

        return $this->respondNoContent();
    }

    /**
     * @param  int  $postId
     * @return bool
     */
    private function _removeRubrics(int $postId): bool
    {
        // Очищаем список категорий
        return $this->PRM->where(['post_id' => $postId])->delete();
    }
}
