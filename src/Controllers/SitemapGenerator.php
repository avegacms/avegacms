<?php

declare(strict_types = 1);

namespace AvegaCms\Controllers;

use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Enums\MetaChangefreq;
use AvegaCms\Utilities\Cms;
use ReflectionException;

class SitemapGenerator extends BaseController
{
    protected array $config  = [];
    protected array $modules = [];

    /**
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->config  = Cms::settings('core.seo');
        $this->modules = model(ModulesModel::class)->getModulesSiteMapSchema();
        helper(['filesystem']);
    }

    /**
     * @param  string  $pointer
     * @param  array  $data
     * @param  bool  $isSiteMap
     * @return bool
     */
    public function xml(string $pointer, array $data = [], bool $isSiteMap = true): bool
    {
        $pointer = explode('.', $pointer);
        $num     = count($pointer);

        if ( ! $this->config['useSitemap'] && $num === 0) {
            return false;
        }
        if ( ! ($module = ($this->modules[$pointer[0]] ?? false))) {
            log_message('error', 'Sitemap pointer module "' . $pointer[0] . '" not found.');
            return false;
        }

        if ($num > 1 && ! ($subModule = ($this->modules[$pointer[0]]['sub'][$pointer[1]] ?? false))) {
            log_message('error', 'Sitemap sub pointer "' . $pointer[0] . '" not found.');
            return false;
        }

        $date = date('Y-m-d');

        if ($num === 1) {
            if (empty($data) && $isSiteMap === true) {
                foreach ($module['sub'] as $key => $item) {
                    $data['links'][] = [
                        'url'        => base_url('uploads/sitemaps/' . ucfirst($key) . '.xml'),
                        'priority'   => 60,
                        'changefreq' => strtolower(MetaChangefreq::Daily->value),
                        'date'       => $date
                    ];
                }
                return $this->_createXml($pointer[0], $data);
            }

            if ( ! empty($data) && $isSiteMap === false) {
                return $this->_createXml($pointer[0], ['links' => $data], false);
            }
        } else {
            if (count($data) <= $this->config['sitemapBatchQty']) {
                return $this->_createXml($pointer[1], ['links' => $data], false);
            } else {
                $chunkLinks = array_chunk($data, $this->config['sitemapBatchQty']);
                $chunkNames = [];
                foreach ($chunkLinks as $key => $links) {
                    $xmlName = $pointer[1] . '_' . ($key + 1);
                    if ($this->_createXml($xmlName, ['links' => $links], false)) {
                        $chunkNames['links'][] = [
                            'url'        => base_url('uploads/sitemaps/' . ucfirst($xmlName) . '.xml'),
                            'priority'   => 60,
                            'changefreq' => strtolower(MetaChangefreq::Daily->value),
                            'date'       => $date
                        ];
                    }
                }
                return $this->_createXml($pointer[1], $chunkNames);
            }
        }

        return false;
    }

    /**
     * @param  string  $file
     * @param  array  $data
     * @param  bool  $isSiteMap
     * @return bool
     */
    private function _createXml(string $file, array $data, bool $isSiteMap = true): bool
    {
        $fileName = ucfirst($file);

        if (file_exists(FCPATH . $file = ('uploads/sitemaps/' . $fileName . '.xml'))) {
            unlink(FCPATH . $file);
        }

        if (is_null($data['isSiteMap'] ?? null)) {
            $data['isSiteMap'] = $isSiteMap;
        }

        if ( ! write_file('./' . $file, view('template/seo/sitemap.php', $data, ['debug' => false]))) {
            log_message('error', "Unable to write the $fileName.xml file");
            return false;
        }

        return true;
    }
}
