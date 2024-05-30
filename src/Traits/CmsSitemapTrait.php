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
     * @param  array  $list
     * @param  array|null  $config
     * @return void
     * @throws Exception
     */
    protected function setModule(string|array $group, array $list, ?array $config = null): void
    {
        helper(['date']);

        if (empty($group)) {
            return;
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');

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
            $xml->asXML(FCPATH . $this->path . $this->moduleName . '_' . $group . '.xml');
        } else {
            foreach ($group as $item) {
                $url = $xml->addChild('sitemap');
                $url->addChild('loc', htmlspecialchars(site_url($item)));
                $url->addChild('lastmod', date(DATE_W3C, strtotime($config['lastmod'] ?? now())));
                $url->addChild('priority', strtolower($config['priority'] ?? SitemapChangefreqs::Monthly->name));
            }
            $xml->asXML(FCPATH . $this->path . $this->moduleName . '.xml');
        }
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
        $xml      = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
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