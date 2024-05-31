<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Enums\{MetaDataTypes, MetaStatuses};
use AvegaCms\Models\Admin\{MetaDataModel, ContentModel, ModulesModel, PermissionsModel, RolesModel};
use ReflectionException;
use RuntimeException;
use Exception;

class CmsModule
{
    /**
     * @param  array  $moduleData
     * @return void
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

        $moduleData['subModules'] = $moduleData['subModules'] ?? [];

        $module = [
            'parent'        => $moduleData['parent'] ?? 0,
            'is_core'       => false,
            'is_plugin'     => false,
            'is_system'     => false,
            'key'           => $moduleData['key'] ?? $slug,
            'slug'          => $slug,
            'class_name'    => $moduleData['className'] ?? '',
            'name'          => $moduleData['name'] ?? $name . '.module.title.main',
            'version'       => $version,
            'description'   => $moduleData['description'] ?? $name . '.module.title.main',
            'extra'         => [],
            'url_pattern'   => isset($moduleData['urlPatterns']) ?
                (is_array($moduleData['urlPatterns']) ?
                    ($moduleData['urlPatterns'][$slug] ?? '') :
                    $moduleData['urlPatterns']) :
                $slug,
            'in_sitemap'    => boolval($moduleData['inSitemap'] ?? 0),
            'active'        => true,
            'created_by_id' => 1,
            'updated_by_id' => 0
        ];

        if (($parentId = $MM->insert($module)) === false) {
            d($MM->errors());
        }

        if ( ! empty($moduleData['subModules'] ?? [])) {
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
                    'in_sitemap'    => boolval($moduleData['inSitemap'] ?? 0),
                    'active'        => true,
                    'created_by_id' => 1,
                    'updated_by_id' => 0
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
                    'updated_by_id' => 0
                ];

                if ($PM->insert($permissions) === false) {
                    d($PM->errors());
                }
            }
        }

        cache()->delete('ModulesMetaData');
    }

    /**
     * @param  string  $name
     * @return string
     */
    public static function prepName(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))));
    }

    /**
     * @param  string  $key
     * @return array|null
     */
    public static function meta(string $key): array|null
    {
        if (($meta = model(ModulesModel::class)->getModulesMeta()[$key] ?? null) === null) {
            log_message('error', "Module metadata not found :: key {$key}");
        }

        return $meta;
    }

    /**
     * @param  string  $key
     * @return array
     */
    public static function parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new RuntimeException('$key cannot be empty');
        }

        $parts[1] = $parts[1] ?? null;
        $parts[2] = $parts[2] ?? null;
        $parts[3] = $parts[3] ?? null;

        return $parts;
    }


    /**
     * @param  string  $key
     * @param  string|null  $title
     * @param  string|null  $url
     * @param  int|null  $parent
     * @param  string|null  $slug
     * @param  array|null  $meta
     * @param  array|null  $meta_sitemap
     * @param  bool  $in_sitemap
     * @param  bool  $use_url_pattern
     * @return mixed
     * @throws ReflectionException|Exception
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
            'parent'          => $parent ?? (($metaData['parent'] != 0) ? $metaData['parent'] : 1),
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
            'in_sitemap'      => boolval($in_sitemap ?? ($metaData['inSitemap'] ?? 0)),
            'use_url_pattern' => $use_url_pattern,
            'publish_at'      => date('Y-m-d H:i:s', now()),
            'created_by_id'   => 1
        ];

        if ($metaId = $MDM->insert($page)) {
            (new ContentModel())->insert(['id' => $metaId]);
        } else {
            d($MDM->errors());
        }

        return $metaId;
    }
}