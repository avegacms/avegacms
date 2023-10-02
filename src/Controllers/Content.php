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
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        $contentSettings = Cms::settings('content');
        $segments        = $this->request->uri->getSegments();
        $filter          = $this->request->getGet() ?? [];
        $locale          = session()->get('avegacms.client.locale.id');

        if (Cms::settings('core.env.useMultiLocales')) {
            unset($segments[0]); // Удаляем языковой сегмент
        }

        $segment = empty($segments) ? '' : array_reverse($segments)[0];

        $meta = $this->initRender(['locale' => $locale, 'segment' => $segment]);

        $template = 'content/';
        $data     = [];

        switch ($meta->meta_type) {
            case MetaDataTypes::Main->value:
                $template .= 'main';
                break;
            case MetaDataTypes::Page->value:
                $template         .= 'page';
                $data['subPages'] = $this->MDM->getSubPages($meta->id);
                break;
            case MetaDataTypes::Rubric->value:
                $template         .= 'rubric';
                $filter['rubric'] = $meta->id;
                $filter['s']      = $filter['s'] ?? '-published';
                $data['posts']    = $this->MDM->getRubricPosts($filter)->paginate($contentSettings['posts']['postsPerPage'] ?? 20);
                $this->pager      = $this->MDM->pager;
                break;
            case MetaDataTypes::Post->value:
                $template .= 'post';
                break;
            default:
                return $this->error404();
        }

        return $this->render($data, $template);
    }
}
