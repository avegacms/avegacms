<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Frontend\MetaDataModel;
use AvegaCms\Enums\MetaDataTypes;

class Content extends AvegaCmsFrontendController
{
    protected MetaDataModel $MDM;

    public function __construct()
    {
        parent::__construct();
        $this->MDM = model(MetaDataModel::class);
    }

    public function index()
    {
        $settings = settings('core.env');

        $segments = $this->request->uri->getSegments();

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

        $this->metaData = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($parentMeta);

        $template = 'template/content/';

        switch ($meta->meta_type) {
            case MetaDataTypes::Main->value:
                $template .= 'main';
                break;
            case MetaDataTypes::Page->value:
                $template .= 'page';
                break;
            case MetaDataTypes::Rubric->value:
                $template .= 'rubric';
                break;
            case MetaDataTypes::Post->value:
                $template .= 'post';
                break;
            default:
                return $this->error404();
        }

        //$data['content'] =

        //dd($meta, $parentMeta, $meta->metaRender(), $meta->breadCrumbs($parentMeta));
        //dd($meta, $parentMeta);


        // TODO 1. Последний сегмент проверяется в slug
        // TODO 1.1 если нет, то 404 (согласно локали)
        // TODO 2. проверяем тип записи (главная|страница|рубрика|пост)
        // TODO 2.1 Проверяем цепочку по parent (если указана страница)
        // TODO 2.2 В случае, если цепочка не активна, то 404
        // TODO 3.1 Формируем мета и breadcrumbs
        // TODO 3.2 Отправляем на вывод
        //dd($segments);

        return $this->render([], $template);
    }
}
