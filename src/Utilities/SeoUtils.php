<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Enums\SitemapChangefreqs;
use AvegaCms\Models\Admin\LocalesModel;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Models\Admin\ModulesModel;
use ReflectionException;
use RuntimeException;

class SeoUtils
{
    public static function Locales(int $id = 0): array
    {
        $locales = array_column(model(LocalesModel::class)->getLocalesList(), null, 'id');

        if ($id > 0 && ! isset($locales[$id])) {
            throw new RuntimeException('Undefined locale');
        }

        return ($id > 0) ? $locales[$id] : $locales;
    }

    public static function LocaleData(int $id): array
    {
        return self::Locales($id)['extra'];
    }

    public static function mainPages(int $id = 0): array
    {
        $pages = array_column(model(MetaDataModel::class)->mainPages(), 'id', 'locale');

        if ($id > 0 && ! isset($pages[$id])) {
            throw new RuntimeException('Undefined main page');
        }

        return ($id > 0) ? $pages[$id] : $pages;
    }

    public static function rubricsList(int $locale = 0, ?string $key = null, ?string $value = null): array
    {
        $rubrics = model(MetaDataModel::class)->getRubrics();

        if ($locale > 0) {
            $rubrics = array_filter(array_map(
                callback: static fn ($item) => $item['locale_id'] === $locale ? $item : null,
                array: $rubrics
            ));
        }

        if (empty($rubrics)) {
            throw new RuntimeException('Undefined rubrics');
        }

        if (null !== $key || null !== $value) {
            $rubrics = array_column($rubrics, $value, $key);
        }

        return $rubrics;
    }

    /**
     * @throws ReflectionException
     */
    public static function sitemap(string $pointer, array $data = [], bool $isSiteMap = true): bool
    {
        $config  = Cms::settings('core.seo');
        $modules = model(ModulesModel::class)->getModulesSiteMapSchema();
        $pointer = explode('.', $pointer);
        $num     = count($pointer);

        if (! ['useSitemap'] && $num === 0) {
            return false;
        }
        if (! ($module = ($modules[$pointer[0]] ?? false))) {
            log_message('error', 'Sitemap pointer module "' . $pointer[0] . '" not found.');

            return false;
        }

        if ($num > 1 && ! ($subModule = ($modules[$pointer[0]]['sub'][$pointer[1]] ?? false))) {
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
                        'changefreq' => strtolower(SitemapChangefreqs::Daily->name),
                        'date'       => $date,
                    ];
                }

                return self::createSitemapXml($pointer[0], $data);
            }

            if (! empty($data) && $isSiteMap === false) {
                return self::createSitemapXml($pointer[0], ['links' => $data], false);
            }
        } else {
            if (count($data) <= $config['sitemapBatchQty']) {
                return self::createSitemapXml($pointer[1], ['links' => $data], false);
            }
            $chunkLinks = array_chunk($data, $config['sitemapBatchQty']);
            $chunkNames = [];

            foreach ($chunkLinks as $key => $links) {
                $xmlName = $pointer[1] . '_' . ($key + 1);
                if (self::createSitemapXml($xmlName, ['links' => $links], false)) {
                    $chunkNames['links'][] = [
                        'url'        => base_url('uploads/sitemaps/' . ucfirst($xmlName) . '.xml'),
                        'priority'   => 60,
                        'changefreq' => strtolower(SitemapChangefreqs::Daily->name),
                        'date'       => $date,
                    ];
                }
            }

            return self::createSitemapXml($pointer[1], $chunkNames);
        }

        return false;
    }

    public static function createSiteMapXml(string $file, array $data, bool $isSiteMap = true): bool
    {
        helper(['filesystem']);
        $fileName = ucfirst($file);

        if (file_exists(FCPATH . $file = ('uploads/sitemaps/' . $fileName . '.xml'))) {
            unlink(FCPATH . $file);
        }

        if (null === ($data['isSiteMap'] ?? null)) {
            $data['isSiteMap'] = $isSiteMap;
        }

        if (! write_file('./' . $file, view('template/seo/sitemap.php', $data, ['debug' => false]))) {
            log_message('error', "Unable to write the {$fileName}.xml file");

            return false;
        }

        return true;
    }
}
