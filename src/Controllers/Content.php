<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Frontend\ContentModel;
use AvegaCms\Enums\MetaDataTypes;
use CodeIgniter\HTTP\ResponseInterface;
use AvegaCms\Utils\Cms;
use ReflectionException;

class Content extends AvegaCmsFrontendController
{
    protected ContentModel $CM;

    public function __construct()
    {
        parent::__construct();
        $this->CM = model(ContentModel::class);
    }

    /**
     * @return ResponseInterface|string
     * @throws ReflectionException
     */
    public function index(): ResponseInterface|string
    {
        $settings = Cms::settings('core.env');
        $contentSettings = Cms::settings('content');
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
            if ( ! empty($segments) && empty($parentMeta = $this->MDM->getContentMetaMap($locale, $segments))) {
                return $this->error404();
            }
        }

        $this->meta = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($meta->meta_type, $parentMeta);

        $template = 'content/';

        switch ($meta->meta_type) {
            case MetaDataTypes::Main->value:
                $template .= 'main';
                break;
            case MetaDataTypes::Page->value:
                $template .= 'page';
                $data['subPages'] = $this->MDM->getSubPages($meta->id);
                break;
            case MetaDataTypes::Rubric->value:
                $template .= 'rubric';
                $filter['rubric'] = $meta->id;
                $filter['s'] = $filter['s'] ?? '-published';
                $data['posts'] = $this->MDM->getRubricPosts($filter)->paginate($contentSettings['posts']['postsPerPage'] ?? 20);
                $this->pager = $this->MDM->pager;
                break;
            case MetaDataTypes::Post->value:
                $template .= 'post';
                break;
            default:
                return $this->error404();
        }

        $data['content'] = $this->CM->find($meta->id);

        return $this->render($data, $template);
    }
}
