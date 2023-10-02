<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use AvegaCms\Enums\{EntityTypes, MetaDataTypes};
use AvegaCms\Utils\{Cms, CmsModule};
use AvegaCms\Entities\Seo\MetaEntity;
use AvegaCms\Entities\ContentEntity;
use AvegaCms\Models\Frontend\{ContentModel, MetaDataModel};
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Pager\Pager;
use RuntimeException;
use ReflectionException;

class AvegaCmsFrontendController extends BaseController
{
    protected string         $metaType     = 'module';
    protected ?string        $moduleKey    = null;
    protected array          $moduleParams = [];
    protected array          $breadCrumbs  = [];
    protected MetaDataModel  $MDM;
    protected ?MetaEntity    $meta         = null;
    protected ?ContentEntity $content      = null;
    protected ?Pager         $pager        = null;

    private readonly array $specialVars;

    public function __construct()
    {
        $this->specialVars = ['meta', 'breadcrumbs', 'pager'];
        $this->MDM         = model(MetaDataModel::class);
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

        return $this->response->setBody(view('template/foundation', $data, $options));
    }

    /**
     * @param  array  $params
     * @return object
     * @throws ReflectionException
     */
    protected function initRender(array $params): object
    {
        $module         = [];
        $this->metaType = strtoupper($this->metaType);
        
        if ($this->metaType === EntityTypes::Module->value) {
            if (($module = CmsModule::meta($this->moduleKey)) === null) {
                return $this->error404();
            }
        }

        $meta = match ($this->metaType) {
            EntityTypes::Content->value => $this->MDM->getContentMetaData($params['locale'], $params['segment']),
            EntityTypes::Module->value  => $this->MDM->getModuleMetaData($module['id'], $params),
            default                     => null
        };

        if ($meta === null || $meta->meta_type === MetaDataTypes::Main->value || empty($parentMeta = $this->MDM->getMetaMap($meta->id))) {
            return $this->error404();
        }

        $this->meta        = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($meta->meta_type, $parentMeta);
        $this->content     = model(ContentModel::class)->getContent($meta->id);

        return $meta;
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
