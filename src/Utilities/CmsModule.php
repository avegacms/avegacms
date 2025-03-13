<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Exceptions\AvegaCmsException;
use AvegaCms\Models\Admin\ContentModel;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Models\Admin\ModulesModel;
use AvegaCms\Models\Admin\PermissionsModel;
use AvegaCms\Models\Admin\RolesModel;
use Exception;
use ReflectionException;
use RuntimeException;

class CmsModule
{
    /**
     * @throws ReflectionException|RuntimeException
     */
    public static function install(array $moduleData): void
    {
        $version = AvegaCms::AVEGACMS_VERSION;

        $MM = model(ModulesModel::class);
        $RM = model(RolesModel::class);
        $PM = model(PermissionsModel::class);

        $name = ucwords($moduleData['slug']);
        $slug = strtolower($moduleData['slug']);

        $moduleData['subModules'] ??= [];

        $module = [
            'parent'      => $moduleData['parent'] ?? 0,
            'is_core'     => false,
            'is_plugin'   => false,
            'is_system'   => false,
            'key'         => $moduleData['key'] ?? $slug,
            'slug'        => $slug,
            'class_name'  => $moduleData['className'] ?? '',
            'name'        => $moduleData['name'] ?? $name . '.module.title.main',
            'version'     => $version,
            'description' => $moduleData['description'] ?? $name . '.module.title.main',
            'extra'       => [],
            'url_pattern' => isset($moduleData['urlPatterns']) ?
                (is_array($moduleData['urlPatterns']) ?
                    ($moduleData['urlPatterns'][$slug] ?? '') :
                    $moduleData['urlPatterns']) :
                $slug,
            'in_sitemap'    => (bool) ($moduleData['inSitemap'] ?? 0),
            'active'        => true,
            'created_by_id' => 1,
            'updated_by_id' => 0,
        ];

        if (($parentId = $MM->insert($module)) === false) {
            d($MM->errors());
        }

        if (! empty($moduleData['subModules'] ?? [])) {
            foreach ($moduleData['subModules'] as $group) {
                $subName    = self::prepName($group);
                $subModules = [
                    'parent'        => $parentId,
                    'is_core'       => false,
                    'is_plugin'     => false,
                    'is_system'     => false,
                    'key'           => $slug . '.' . $group,
                    'slug'          => $group,
                    'class_name'    => '',
                    'name'          => $name . '.module.title.' . $subName,
                    'version'       => $version,
                    'description'   => $name . '.module.title.' . $subName,
                    'extra'         => [],
                    'url_pattern'   => $moduleData['urlPatterns'][$group] ?? '',
                    'in_sitemap'    => (bool) ($moduleData['inSitemap'] ?? 0),
                    'active'        => true,
                    'created_by_id' => 1,
                    'updated_by_id' => 0,
                ];
                if ($MM->insert($subModules) === false) {
                    d($MM->errors());
                }
            }
        }

        array_unshift($moduleData['subModules'], $slug);

        $modules = $MM->select(['id', 'slug', 'parent'])->whereIn('slug', $moduleData['subModules'])->findAll();

        $roles = $RM->whereIn('role', $moduleData['roles'])->findColumn('id');

        foreach ($roles as $role) {
            foreach ($modules as $module) {
                $permissions = [
                    'role_id'       => $role,
                    'parent'        => $module->parent,
                    'module_id'     => $module->id,
                    'is_module'     => true,
                    'is_system'     => false,
                    'is_plugin'     => false,
                    'slug'          => $module->slug,
                    'access'        => true,
                    'self'          => false,
                    'create'        => true,
                    'read'          => true,
                    'update'        => true,
                    'delete'        => true,
                    'moderated'     => false,
                    'settings'      => true,
                    'extra'         => [],
                    'created_by_id' => 1,
                    'updated_by_id' => 0,
                ];

                if ($PM->insert($permissions) === false) {
                    d($PM->errors());
                }
            }
        }

        cache()->delete('ModulesMetaData');
    }

    public static function prepName(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))));
    }

    public static function meta(string $key): ?array
    {
        if (($meta = (new ModulesModel())->getModulesMeta()[$key] ?? null) === null) {
            log_message('error', "Module metadata not found :: key {$key}");
        }

        return $meta;
    }

    public static function pageMeta(int $moduleId, string $slug): ?object
    {
        return (new MetaDataModel())->pageModuleMeta($moduleId, $slug);
    }

    public static function parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new RuntimeException('$key cannot be empty');
        }

        $parts[1] ??= null;
        $parts[2] ??= null;
        $parts[3] ??= null;

        return $parts;
    }

    /**
     * @throws Exception|ReflectionException
     */
    public static function createModulePage(
        string $key,
        ?string $title = null,
        ?string $url = null,
        ?int $parent = null,
        ?string $slug = null,
        ?array $meta = null,
        ?array $meta_sitemap = null,
        bool $in_sitemap = false,
        bool $use_url_pattern = false
    ): mixed {
        helper(['date']);

        $metaData = self::meta($key);
        $MDM      = new MetaDataModel();
        $page     = [
            'parent'          => $parent ?? (($metaData['parent'] !== 0) ? $metaData['parent'] : 1),
            'locale_id'       => 1, // TODO сделать настраиваемой
            'module_id'       => $metaData['id'] ?? $parent,
            'slug'            => $slug ?? $metaData['slug'],
            'creator_id'      => 1,
            'item_id'         => 0,
            'title'           => $title ?? $metaData['name'],
            'url'             => $url ?? $metaData['url'],
            'meta'            => is_array($meta) ? $meta : [],
            'meta_sitemap'    => is_array($meta_sitemap) ? $meta_sitemap : [],
            'status'          => MetaStatuses::Publish->name,
            'meta_type'       => MetaDataTypes::Module->name,
            'in_sitemap'      => (bool) ($in_sitemap ?? ($metaData['inSitemap'] ?? 0)),
            'use_url_pattern' => $use_url_pattern,
            'publish_at'      => date('Y-m-d H:i:s', now()),
            'created_by_id'   => 1,
        ];

        if (($metaId = $MDM->insert($page)) === false) {
            throw new AvegaCmsException($MDM->errors());
        }
        (new ContentModel())->insert(['id' => $metaId]);

        return $metaId;
    }

    public static function getModulePageMeta(
        int|string $moduleKey,
        ?string $slug = null,
        ?int $localeId = null,
        ?int $parent = null,
        ?int $itemId = null,
        ?string $url = null
    ): ?object {
        if (($moduleKey = is_int($moduleKey) ? $moduleKey : (self::meta($moduleKey)['id'] ?? null)) === null) {
            return null;
        }

        $filter = [
            'parent'    => $parent,
            'locale_id' => $localeId,
            'module_id' => $moduleKey,
            'slug'      => $slug,
            'item_id'   => $itemId,
            'url'       => $url,
        ];

        return (new MetaDataModel())->filter(array_filter($filter, static fn ($value) => $value !== null))->first();
    }
}
