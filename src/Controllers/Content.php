<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

class Content extends AvegaCmsFrontendController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $settings = settings('core.env');

        $segments = $this->request->uri->getSegments();

        if ($settings['useMultiLocales']) {
            // Удаляем языковой сегмент
            unset($segments[0]);
        }
        // TODO 1. Последний сегмент проверяется в slug
        // TODO 1.1 если нет, то 404 (согласно локали)
        // TODO 2. проверяем тип записи (главная|страница|рубрика|пост)
        // TODO 2.1 Проверяем цепочку по parent (если )
        // TODO 3. - формируем мета и
        dd($segments);

        return $this->render([]);
    }
}
