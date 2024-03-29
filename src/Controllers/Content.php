<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Enums\MetaDataTypes;
use CodeIgniter\HTTP\ResponseInterface;
use ReflectionException;

class Content extends AvegaCmsFrontendController
{
    protected string $metaType = 'content';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function index(): ResponseInterface
    {
        $filter   = $this->request->getGet() ?? [];
        $template = 'content/';
        $data     = [];

        switch ($this->dataEntity->metaType) {
            case MetaDataTypes::Main->value:
                $template .= 'main';
                break;
            case MetaDataTypes::Page->value:
                $template         .= 'page';
                $data['subPages'] = $this->MDM->getSubPages($this->dataEntity->id);
                break;
            case MetaDataTypes::Rubric->value:
                $template         .= 'rubric';
                $filter['rubric'] = $this->dataEntity->id;
                $filter['s']      = $filter['s'] ?? '-published';
                $data['posts']    = $this->MDM->getRubricPosts($filter)->paginate($contentSettings['posts']['postsPerPage'] ?? 20);
                $this->pager      = $this->MDM->pager;
                break;
            case MetaDataTypes::Post->value:
                $template .= 'post';
                break;
            default:
                $this->error404();
        }

        return $this->render($data, $template);
    }
}
