<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Frontend\{MetaDataModel, ContentModel};
use AvegaCms\Enums\MetaDataTypes;
use CodeIgniter\HTTP\ResponseInterface;

class Content extends AvegaCmsFrontendController
{
    protected ContentModel  $CM;
    protected MetaDataModel $MDM;

    public function __construct()
    {
        parent::__construct();
        $this->CM = model(ContentModel::class);
        $this->MDM = model(MetaDataModel::class);
    }

    /**
     * @return ResponseInterface|string
     */
    public function index(): ResponseInterface|string
    {
        $settings = settings('core.env');
        $contentSettings = settings('content');
        $segments = $this->request->uri->getSegments();
        $filter = $this->request->getGet() ?? [];

        if ($settings['useMultiLocales']) {
            unset($segments[0]); // Удаляем языковой сегмент
        }

        $locale = session()->get('avegacms.client.locale.id');

        $segment = empty($segments) ? '' : array_reverse($segments)[0];

        if (($meta = $this->MDM->getContentMetaData($locale, $segment)) === null) {
            return $this->error404();
        }
        $parentMeta = [];
        // Проверяем цепочку записей
        if ($meta->meta_type !== MetaDataTypes::Main->value) {
            array_pop($segments);
            if (empty($parentMeta = $this->MDM->getContentMetaMap($locale, $segments))) {
                return $this->error404();
            }
        }

        $this->meta = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($parentMeta);

        $template = 'template/content/';

        switch ($meta->meta_type) {
            case MetaDataTypes::Main->value:
                $template .= 'main';
                break;
            case MetaDataTypes::Page->value:
                $template .= 'page';
                $data['subPages'] = $this->CM->getSubPages($meta->id);
                break;
            case MetaDataTypes::Rubric->value:
                $template .= 'rubric';
                $filter['rubric'] = $meta->id;
                $data['posts'] = $this->PRM->getRubricPosts($filter)->paginate($contentSettings['posts']['postsPerPage'] ?? 20);
                $this->pager = $this->PRM->pager();
                break;
            case MetaDataTypes::Post->value:
                $template .= 'post';
                break;
            default:
                return $this->error404();
        }

        $data['content'] = $this->CM->find($meta->id);

        // TODO 1. Добавить публичное имя в users
        // TODO 2. Сделать базовую рубрику для поста
        // TODO 3. Сделать 404 страницу
        // TODO 4. Добавить отображение $parentMeta для постов + при редактировании/удалении сделать проверку

        return $this->render($data, $template);
    }
}
