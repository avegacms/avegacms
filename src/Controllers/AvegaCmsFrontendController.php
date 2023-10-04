<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use Config\Services;
use AvegaCms\Enums\{EntityTypes, MetaDataTypes};
use AvegaCms\Utils\{Cms, CmsModule};
use AvegaCms\Entities\Seo\MetaEntity;
use AvegaCms\Entities\{ContentEntity, MetaDataEntity};
use AvegaCms\Models\Frontend\{ContentModel, MetaDataModel};
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Pager\Pager;
use RuntimeException;
use ReflectionException;

class AvegaCmsFrontendController extends BaseController
{
    protected string          $metaType    = 'module';
    protected ?string         $moduleKey   = null;
    protected array           $breadCrumbs = [];
    protected MetaDataModel   $MDM;
    protected ?MetaDataEntity $dataEntity  = null;
    protected ?MetaEntity     $meta        = null;
    protected ?ContentEntity  $content     = null;
    protected ?Pager          $pager       = null;

    /**
     * @throws ReflectionException
     */
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
    public function render(array $pageData, string $view = '', array $options = []): ResponseInterface
    {
        $parentMeta = [];

        if ($this->dataEntity === null || $this->dataEntity->meta_type !== MetaDataTypes::Main->value
            && empty($parentMeta = $this->MDM->getMetaMap($this->dataEntity->id))) {
            return $this->error404();
        }

        $this->meta        = $this->dataEntity->metaRender();
        $this->breadCrumbs = $this->dataEntity->breadCrumbs($this->dataEntity->meta_type, $parentMeta);
        $this->content     = model(ContentModel::class)->getContent($this->dataEntity->id);

        $data['data']        = $pageData;
        $data['content']     = $this->content;
        $data['meta']        = $this->meta;
        $data['breadcrumbs'] = $this->breadCrumbs;
        $data['pager']       = $this->pager;

        $data['template'] = null;

        if (Cms::settings('core.env.useViewData')) {
            if ( ! file_exists($file = APPPATH . 'Views/' . ($view = 'template/' . $view) . '.php')) {
                throw new RuntimeException("File $file not found");
            }
            $data['template'] = view($view, $data, $options);
        } else {
            unset($data['template']);
        }

        unset($pageData);

        return response()->setBody(view('template/foundation', $data, $options));
    }


    /**
     * @return ResponseInterface|null
     * @throws ReflectionException
     */
    protected function initRender(): ?ResponseInterface
    {
        $module         = $params = [];
        $this->metaType = strtoupper($this->metaType);
        $segments       = Services::request()->uri->getSegments();

        if ($this->metaType === EntityTypes::Module->value) {
            if (($module = CmsModule::meta($this->moduleKey)) === null || empty($segments)) {
                return $this->error404();
            }

            if ( ! empty($patternSegment = explode('/', $module['url_pattern']))) {
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
                }
            } else {
                $params['slug'] = $module['slug'];
            }
        } else {
            $params['locale']  = session()->get('avegacms.client.locale.id');
            $params['segment'] = empty($segments) ? '' : array_reverse($segments)[0];
        }

        $this->dataEntity = match ($this->metaType) {
            EntityTypes::Content->value => $this->MDM->getContentMetaData($params['locale'], $params['segment']),
            EntityTypes::Module->value  => $this->MDM->getModuleMetaData($module['id'], $params)
        };

        if ($this->dataEntity === null) {
            $this->dataEntity = $this->MDM->getContentMetaData(session('avegacms.client.locale.id'), 'page-not-found');
        }

        return null;
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function error404(): ResponseInterface
    {
        $this->meta        = $this->dataEntity->metaRender();
        $this->breadCrumbs = $this->dataEntity->breadCrumbs($this->dataEntity->meta_type);

        response()->setStatusCode(404);

        return $this->render([], 'content/404');
    }
}
