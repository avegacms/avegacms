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
        $path = FCPATH . $this->path . $this->moduleName;

        if (is_string($group)) {
            if (empty($list)) {
                return;
            }
            foreach ($list as $item) {
                $url = $xml->addChild('sitemap');
                $url->addChild('loc', htmlspecialchars(site_url($item->url)));
                $url->addChild('lastmod', date(DATE_W3C, strtotime($config['lastmod'] ?? now())));
                $url->addChild('priority', strtolower($config['priority'] ?? SitemapChangefreqs::Monthly->name));
            }
            $path .= '_' . $group . '.xml';
        } else {
            $lastmod = now();
            if ($config['lastmod'] ?? false) {
                $lastmod = strtotime($config['lastmod']);
            }

            foreach ($group as $item) {
                $url = $xml->addChild('sitemap');
                $url->addChild('loc', htmlspecialchars(site_url($this->path . $item . '.xml')));
                $url->addChild('lastmod', date(DATE_W3C, $lastmod));
                $url->addChild('priority', strtolower($config['priority'] ?? SitemapChangefreqs::Monthly->name));
            }

            $path .= '.xml';

            if ($this->moduleName === null && empty($list)) {
                $path = 'sitemap.xml';
            }
        }

        $xml->asXML($path);
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

        if (is_numeric($qtyElements)) {
            $qtyElements = Cms::settings('core.seo.sitemapBatchQty');
        }

        $groupUrl = (object) [];
        $list     = array_chunk($list, $qtyElements);
        $xml      = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $i        = 0;

        foreach ($list as $chunk) {
            $i++;
            foreach ($chunk as $item) {
                $url = $xml->addChild('url');
                $url->addChild('loc', site_url($item->url));
                $url->addChild('lastmod', date(DATE_W3C, strtotime($item->lastmod)));
                $url->addChild('changefreq', $item->changefreq);
                $url->addChild('priority', $item->priority);
            }
            $groupUrl[]->url = $itemUrl = $this->path . $this->moduleName . '_' . $group . $i . '.xml';
            $xml->asXML(FCPATH . $itemUrl);
        }

        if ( ! empty($groupUrl)) {
            $this->setModule($group, (array) $groupUrl, $groupConfig);
        }
    }
}