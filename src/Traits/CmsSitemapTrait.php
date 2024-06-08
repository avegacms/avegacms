<?php

declare(strict_types = 1);

namespace AvegaCms\Traits;

use AvegaCms\Utilities\Cms;
use AvegaCms\Enums\SitemapChangefreqs;
use SimpleXMLElement;
use ReflectionException;
use Exception;

trait CmsSitemapTrait
{
    protected string $path       = 'uploads/sitemaps/';
    private ?string  $moduleName = null;

    /**
     * @param  string|array  $group
     * @param  array|null  $list
     * @param  array|null  $config
     * @return void
     * @throws Exception
     */
    protected function setModule(string|array $group, ?array $list = null, ?array $config = null): void
    {
        helper(['date']);

        if (empty($group)) {
            return;
        }

        $xml  = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
        $data = [];
        $path = $this->path . $this->moduleName;

        if (is_string($group)) {
            if (empty($list)) {
                return;
            }
            $data = $list;
            $path .= '_' . $group . '.xml';
        } elseif (is_array($group)) {
            foreach ($group as $item) {
                $data[]['url'] = strtolower($path . (is_null($this->moduleName) ? '' : '_') . $item . '.xml');
            }
            $path .= '.xml';
        } else {
            return;
        }

        $lastmod = now();
        if ($config['lastmod'] ?? false) {
            $lastmod = strtotime($config['lastmod']);
        }

        foreach ($data as $item) {
            $url = $xml->addChild('sitemap');
            $url->addChild('loc', htmlspecialchars(site_url($item['url'])));
            $url->addChild('lastmod', date(DATE_W3C, $lastmod));
            $url->addChild('priority', strtolower($config['priority'] ?? SitemapChangefreqs::Monthly->name));
        }

        if ($this->moduleName === null && $list === null) {
            $path = 'sitemap.xml';
        }

        $this->_checkFolder();

        $xml->asXML(FCPATH . strtolower($path));
    }

    /**
     * @param  string  $group
     * @param  array  $list
     * @param  int|null  $qtyElements
     * @param  array|null  $groupConfig
     * @return void
     * @throws Exception|ReflectionException
     */
    protected function setGroup(string $group, array $list, ?int $qtyElements = null, ?array $groupConfig = null): void
    {
        if (empty($group) || empty($list)) {
            return;
        }

        if ( ! is_numeric($qtyElements)) {
            $qtyElements = Cms::settings('core.seo.sitemapBatchQty');
        }

        if (count($list) > $qtyElements) {
            $groupUrl = [];
            $i        = 0;
            $list     = array_chunk($list, $qtyElements);
            foreach ($list as $chunk) {
                $i++;
                $groupUrl[]['url'] = $this->_createUrlSet($chunk, $group, $i);
            }

            if ( ! empty($groupUrl)) {
                $this->setModule($group, $groupUrl, $groupConfig);
            }
        } else {
            $this->_createUrlSet($list, $group);
        }
    }

    /**
     * @param  array  $list
     * @param  string  $group
     * @param  int  $step
     * @return string
     */
    private function _createUrlSet(array $list, string $group, int $step = 0): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($list as $item) {
            $url = $xml->addChild('url');
            $url->addChild('loc', site_url($item->url));
            $url->addChild('lastmod', date(DATE_W3C, strtotime($item->lastmod)));
            $url->addChild('changefreq', $item->changefreq ?? SitemapChangefreqs::Monthly->name);
            $url->addChild('priority', (string) ($item->priority ?? 50));
        }

        $sitemapFile = $this->path . $this->moduleName . '_' . $group;

        if ($step > 0) {
            $sitemapFile .= '_' . $step;
        }

        $sitemapFile .= '.xml';

        $sitemapFile = strtolower($sitemapFile);

        if ($xml->asXML(FCPATH . $sitemapFile) !== true) {
            $sitemapFile = '';
        }

        return $sitemapFile;
    }

    /**
     * @return void
     */
    private function _checkFolder(): void
    {
        $path = substr_replace(FCPATH . $this->path, '', -1);

        if ( ! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}