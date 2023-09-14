<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Frontend\{MetaDataModel, ContentModel};
use AvegaCms\Enums\MetaDataTypes;

class Content extends AvegaCmsFrontendController
{
    protected MetaDataModel $MDM;
    protected ContentModel  $CM;

    public function __construct()
    {
        parent::__construct();
        $this->MDM = model(MetaDataModel::class);
        $this->CM = model(ContentModel::class);
    }

    public function index()
    {
        $settings = settings('core.env');
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
            $parentMeta = match ($meta->meta_type) {
                MetaDataTypes::Page->value,
                MetaDataTypes::Rubric->value => $this->MDM->getContentMetaMap($locale, $segments),
                MetaDataTypes::Post->value   => []
            };

            if (empty($parentMeta)) {
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
                // TODO Список постов и пагинация
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
