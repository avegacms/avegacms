<?php

declare(strict_types = 1);

namespace AvegaCms\Libraries\Sitemap;

use AvegaCms\Models\Admin\ModulesModel;

class Sitemap
{
    protected ?string $moduleName;
    protected ?string $moduleSlug;

    public function __construct(?string $moduleName = null, ?string $moduleSlug = null)
    {
        $this->moduleName = $moduleName;
        $this->moduleSlug = $moduleSlug;
    }

    /**
     * Метод в зависимости от переданных данных выполняет:
     * 1. Глобальное обновление всей карты сайта
     * 2. Обновления карты сайта конкретного модуля
     * 3. Обновления карты сайта группы модуля
     *
     * @return void
     */
    public function run(): void
    {
        $modules = (new ModulesModel())->getModulesMeta();

        if ( ! is_null($this->moduleName) && array_key_exists($this->moduleName, $modules)) {
            $module = $modules[$this->moduleName];
            if ($module['in_sitemap'] === true && $module['parent'] === 0) {
                $this->generate($module['class_name']);
            }
        } else {
            foreach ($modules as $module) {
                if ($module['in_sitemap'] === true && $module['parent'] === 0) {
                    $this->generate($module['class_name']);
                }
            }
        }
    }

    /**
     * @param  string|null  $className
     * @return void
     */
    protected function generate(?string $className = null): void
    {
        if ( ! is_null($className)) {
            if ($className === 'Content') {
                $className = "AvegaCms\\Controllers\\Content\\Sitemap";
            } else {
                $className = "Modules\\{$className}\\Controllers\\Sitemap";
            }

            if (class_exists($className)) {
                $sitemap = new $className();
                if (method_exists($sitemap, 'generate')) {
                    $sitemap->generate($className, $this->moduleSlug);
                }
            }
        }
    }
}