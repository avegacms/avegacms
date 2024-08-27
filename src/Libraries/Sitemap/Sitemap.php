<?php

declare(strict_types=1);

namespace AvegaCms\Libraries\Sitemap;

use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Traits\AvegaCmsSitemapTrait;
use Exception;

class Sitemap
{
    use AvegaCmsSitemapTrait;

    protected ?string $module;
    protected ?string $moduleSlug;

    public function __construct(?string $moduleName = null, ?string $moduleSlug = null)
    {
        $this->module     = $moduleName;
        $this->moduleSlug = $moduleSlug;
    }

    /**
     * Метод в зависимости от переданных данных выполняет:
     * 1. Глобальное обновление всей карты сайта
     * 2. Обновления карты сайта конкретного модуля
     * 3. Обновления карты сайта группы модуля
     *
     * @throws Exception
     */
    public function run(): void
    {
        $modules = (new ModulesModel())->getModulesMeta();

        if (null !== $this->module && array_key_exists($key = strtolower($this->module), $modules)) {
            $module = $modules[$key];
            if ($module['in_sitemap'] === true && $module['parent'] === 0) {
                $this->generate($module['class_name']);
            }
        } else {
            $sitemapGlobal = [];

            foreach ($modules as $module) {
                if ($module['in_sitemap'] === true && $module['parent'] === 0) {
                    // Собираем общий список основных модулей
                    $sitemapGlobal[] = $module['class_name'];
                }
            }
            $this->moduleName = null;
            // Создаём sitemap.xml
            $this->setModule($sitemapGlobal);

            foreach ($sitemapGlobal as $item) {
                $this->generate($item);
            }
        }
    }

    protected function generate(?string $className = null): void
    {
        if (null !== $className) {
            if ($className === 'Pages') {
                $classNamespace = 'AvegaCms\\Controllers\\Sitemap';
            } else {
                $classNamespace = "Modules\\{$className}\\Controllers\\Sitemap";
            }
            if (class_exists($classNamespace)) {
                $sitemap = new $classNamespace();
                if (method_exists($sitemap, 'generate')) {
                    $sitemap->generate($this->moduleSlug);
                }
            }
        }
    }
}
