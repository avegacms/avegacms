<?php

declare(strict_types=1);

namespace AvegaCms\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Pager\Pager;
use RuntimeException;

use AvegaCms\Entities\Seo\MetaEntity;
use AvegaCms\Models\Frontend\MetaDataModel;

class AvegaCmsFrontendController extends BaseController
{
    protected MetaDataModel $MDM;
    protected ?MetaEntity   $meta        = null;
    protected array         $breadCrumbs = [];
    protected ?Pager        $pager       = null;

    private readonly array $specialVars;

    public function __construct()
    {
        helper(['avegacms']);
        $this->specialVars = ['meta', 'breadcrumbs', 'pager'];
        $this->MDM = model(MetaDataModel::class);
    }


    public function render(array $data, string $view = '', array $options = []): string
    {
        if ( ! empty($arr = array_flip(array_intersect_key(array_flip($this->specialVars), $data)))) {
            throw new RuntimeException('Attempt to overwrite system variables: ' . implode(',', $arr));
        }

        $data['meta'] = $this->meta;
        $data['breadcrumbs'] = $this->breadCrumbs;
        $data['pager'] = $this->pager;

        $data['template'] = null;

        if (settings('core.env.useViewData')) {
            if ( ! file_exists($file = APPPATH . 'Views/' . ($view = 'template/' . $view) . '.php')) {
                throw new RuntimeException("File {$file} not found");
            }
            $data['template'] = view($view, $data, $options);
        }

        return view('template/foundation', $data, $options);
    }

    /**
     * @return ResponseInterface
     */
    public function error404(): ResponseInterface
    {
        $meta = $this->MDM->getContentMetaData(session('avegacms.client.locale.id'), 'page-not-found');

        $this->meta = $meta->metaRender();
        $this->breadCrumbs = $meta->breadCrumbs($meta->meta_type);

        return $this->response->setStatusCode(404)->setBody($this->render([], 'content/404'));
    }
}
