<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Enums\{EntityTypes, MetaDataTypes};
use AvegaCms\Utilities\{Cms, CmsModule, PageSeoBuilder};
use AvegaCms\Models\Frontend\{ContentModel, MetaDataModel};
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Pager\Pager;
use JetBrains\PhpStorm\NoReturn;
use RuntimeException;
use ReflectionException;

class AvegaCmsFrontendController extends BaseController
{
    protected string        $metaType        = 'Module';
    protected bool          $useTemplateMeta = false; // Флаг использования кастомных метаданных
    protected ?string       $moduleKey       = null; // Уникальный ключ модуля исп. в таблице modules
    protected array         $metaParams      = []; // Массив для мета-параметров поиска в metadata
    protected array         $breadCrumbs     = [];
    protected MetaDataModel $MDM;
    protected object|null   $dataEntity      = null;
    protected object|null   $parentMeta      = null;
    protected ?array        $customerContent = null; // Массив пользовательского контента
    protected ?array        $content         = null;
    protected ?Pager        $pager           = null;

    /**
     * @throws ReflectionException
     */
    #[NoReturn]
    public function __construct()
    {
        $this->MDM = model(MetaDataModel::class);
        $this->initRender();
    }

    /**
     * @param  array  $pageData
     * @param  string  $view
     * @param  array  $options
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function render(array $pageData = [], string $view = '', array $options = []): ResponseInterface
    {
        $PSB = new PageSeoBuilder($this->dataEntity);

        $this->breadCrumbs = $PSB->breadCrumbs($this->dataEntity->meta_type, $this->parentMeta);

        if ($this->content === null) {
            $this->content = ($this->customerContent === null) ? (new ContentModel())->getContent($this->dataEntity->id) : $this->customerContent;
        }

        $data['data']        = $pageData;
        $data['content']     = $this->content;
        $data['meta']        = $PSB->meta();
        $data['breadcrumbs'] = $this->breadCrumbs;
        $data['pager']       = $this->pager;
        $data['template']    = null;
        $eventData           = (object) [];

        Events::trigger('initPageRender', $eventData);
        $data = [...$data, ...((array) $eventData)];

        if (Cms::settings('core.env.useViewData')) {
            if ( ! file_exists($file = APPPATH . 'Views/' . ($view = 'template/' . $view) . '.php')) {
                throw new RuntimeException("File $file not found");
            }
            $data['template'] = view($view, $data, $options);
        } else {
            unset($data['template']);
        }

        unset($pageData, $session);

        return response()->setBody(view('template/foundation', $data, $options));
    }


    /**
     * @return ResponseInterface|null
     * @throws ReflectionException
     */
    #[NoReturn]
    protected function initRender(): ?ResponseInterface
    {
        $module   = $params = [];
        $segments = request()->getUri()->getSegments();

        if (($this->metaType = ucfirst($this->metaType)) === EntityTypes::Module->name) {
            if ($this->moduleKey === null || ($module = CmsModule::meta($this->moduleKey)) === null || empty($segments)) {
                $this->error404();
            }

            if ( ! $this->useTemplateMeta) {
                if ( ! empty($module['urlPattern']) && $patternSegment = explode('/', $module['urlPattern'])) {
                    foreach ($patternSegment as $k => $val) {
                        if (isset($segments[$k]) && $segments[$k] !== $val) {
                            $params[$val] = $segments[$k];
                        }
                    }

                    if ( ! empty($params)) {
                        foreach ($params as $key => $value) {
                            $newKey = str_replace(['{', '}'], '', $key);
                            unset($params[$key]);
                            $params[$newKey] = $value;
                        }

                        if (isset($params['id']) && is_numeric($params['id']) && $params['id'] > 0) {
                            $id = $params['id'];
                            unset($params);
                            $params['id'] = $id;
                        }
                    } else {
                        $params['slug'] = empty($segments) ? '' : array_reverse($segments)[0];
                    }
                } else {
                    if ((array_slice($segments, -2, 1)[0] ?? null) === $module['slug']) {
                        $params['slug'] = end($segments);
                    } else {
                        $params['slug'] = $module['slug'];
                    }
                }
            }
        } else {
            $params['locale']  = session()->get('avegacms.client.locale.id');
            $params['segment'] = empty($segments) ? '' : end($segments);
        }

        // Проверяем были ли переданы доп. мета параметры для поиска
        if ( ! empty($this->metaParams) && ($this->metaType === EntityTypes::Module->name)) {
            $params = [...$params, ...$this->metaParams];
        }

        $this->dataEntity = match ($this->metaType) {
            EntityTypes::Content->name => $this->MDM->getContentMetaData($params['locale'], $params['segment']),
            EntityTypes::Module->name  => $this->MDM->getModuleMetaData($module['id'], $params)
        };

        if ($this->dataEntity === null) {
            $this->error404();
        }

        if ($this->dataEntity->meta_type !== MetaDataTypes::Main->name
            && empty($this->parentMeta = $this->MDM->getMetaMap($this->dataEntity->parent ?? $this->dataEntity->id,
                $this->dataEntity->id))) {
            $this->error404();
        }

        return null;
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    #[NoReturn]
    public function error404(): void
    {
        $this->dataEntity = $this->MDM->getContentMetaData404(session('avegacms.client.locale.id') ?? 1);
        response()->setStatusCode(404);
        $this->render([], 'content/404')->send();
        exit();
    }
}
