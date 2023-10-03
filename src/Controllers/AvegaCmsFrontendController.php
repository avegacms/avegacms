<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use Config\Services;
use AvegaCms\Enums\{EntityTypes, MetaDataTypes};
use AvegaCms\Utils\{Cms, CmsModule};
use AvegaCms\Entities\Seo\{DataEntity, MetaEntity};
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

    private readonly array $specialVars;

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->specialVars = ['meta', 'breadcrumbs', 'pager'];
        $this->MDM         = model(MetaDataModel::class);
        $this->dataEntity  = $this->initRender();
    }

    /**
     * @param  array  $pageData
     * @param  string  $view
     * @param  array  $options
     * @return ResponseInterface|string
     * @throws ReflectionException
     */
    public function render(array $pageData, string $view = '', array $options = []): ResponseInterface|string
    {
        if ( ! empty($arr = array_flip(array_intersect_key(array_flip($this->specialVars), $pageData)))) {
            throw new RuntimeException('Attempt to overwrite system variables: ' . implode(',', $arr));
        }

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

        return view('template/foundation', $data, $options);
    }

    /**
     * @return ResponseInterface|MetaDataEntity
     * @throws ReflectionException
     */
    protected function initRender(): ResponseInterface|MetaDataEntity
    {
        $module         = $params = [];
        $this->metaType = strtoupper($this->metaType);

        $segments = Services::request()->uri->getSegments();

        if ($this->metaType === EntityTypes::Module->value) {
            if (($module = CmsModule::meta($this->moduleKey)) === null) {
                return $this->error404();
            }

            if ( ! empty($patternSegment = explode('/', $module['url_pattern']))) {
                $params = array_filter(array_combine($patternSegment, $segments), function ($k, $v) {
                    return $k !== $v;
                }, ARRAY_FILTER_USE_BOTH);

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

        $dataEntity = match ($this->metaType) {
            EntityTypes::Content->value => $this->MDM->getContentMetaData($params['locale'], $params['segment']),
            EntityTypes::Module->value  => $this->MDM->getModuleMetaData($module['id'], $params),
            default                     => null
        };

        if ($dataEntity === null
            || $dataEntity->meta_type === MetaDataTypes::Main->value
            || empty($parentMeta = $this->MDM->getMetaMap($dataEntity->id))) {
            return $this->error404();
        }

        $this->meta        = $dataEntity->metaRender();
        $this->breadCrumbs = $dataEntity->breadCrumbs($dataEntity->meta_type, $parentMeta);
        $this->content     = model(ContentModel::class)->getContent($dataEntity->id);

        return $dataEntity;
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function error404(): ResponseInterface
    {
        $meta = $this->MDM->getContentMetaData(session('avegacms.client.locale.id'), 'page-not-found');

        $this->meta        = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($meta->meta_type);

        return $this->response->setStatusCode(404)->setBody($this->render([], 'content/404'));
    }
}
