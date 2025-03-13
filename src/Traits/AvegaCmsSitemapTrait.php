<?php

declare(strict_types=1);

namespace AvegaCms\Traits;

use AvegaCms\Enums\SitemapChangefreqs;
use AvegaCms\Utilities\Cms;
use Exception;
use ReflectionException;
use SimpleXMLElement;

trait AvegaCmsSitemapTrait
{
    protected string $path      = 'uploads/sitemaps/';
    private ?string $moduleName = null;

    /**
     * @throws Exception
     */
    protected function setModule(array|string $group, ?array $list = null, ?array $config = null): void
    {
        helper(['date']);

        if (is_array($group) && empty($group)) {
            return;
        }

        $data = [];
        $xml  = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
        $path = $this->path . $this->moduleName;

        if (is_string($group)) {
            if (empty($list)) {
                return;
            }
            $data = $list;
            $path .= (($group !== '') ? '_' . $group : '') . '.xml';
        } elseif (is_array($group)) {
            foreach ($group as $item) {
                $data[]['url'] = strtolower($path . (null === $this->moduleName ? '' : '_') . $item . '.xml');
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
            $url->addChild('loc', htmlspecialchars(site_url($item->url ?? $item['url'])));
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
     * @throws Exception|ReflectionException
     */
    protected function setGroup(
        array $list,
        string $group = '',
        ?int $qtyElements = null,
        ?array $groupConfig = null
    ): void {
        if (empty($list)) {
            return;
        }

        if (! is_numeric($qtyElements)) {
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

            if (! empty($groupUrl)) {
                $this->setModule(
                    $group,
                    $groupUrl,
                    $groupConfig
                );
            }
        } else {
            $this->_createUrlSet($list, $group);
        }
    }

    private function _createUrlSet(array $list, string $group = '', int $step = 0): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach ($list as $item) {
            $url = $xml->addChild('url');
            $url->addChild('loc', site_url($item->url));
            $url->addChild('lastmod', date(DATE_W3C, strtotime($item->lastmod)));
            $url->addChild('changefreq', $item->changefreq ?? SitemapChangefreqs::Monthly->name);
            $url->addChild('priority', (string) ($item->priority ?? 50));
        }

        $sitemapFile = $this->path . $this->moduleName . ($group === '' ? '' : '_' . $group);

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

    private function _checkFolder(): void
    {
        $path = substr_replace(FCPATH . $this->path, '', -1);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
