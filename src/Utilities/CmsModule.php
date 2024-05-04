<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Enums\{MetaDataTypes, MetaStatuses};
use AvegaCms\Models\Admin\{MetaDataModel, ContentModel, ModulesModel, PermissionsModel, RolesModel};
use ReflectionException;
use RuntimeException;

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
            'is_core'       => 0,
            'is_plugin'     => 0,
            'is_system'     => 0,
            'key'           => $moduleData['key'] ?? $slug,
            'slug'          => $slug,
            'class_name'    => $moduleData['className'] ?? '',
            'name'          => $moduleData['name'] ?? $name . '.module.title.main',
            'version'       => $version,
            'description'   => $moduleData['description'] ?? $name . '.module.title.main',
            'extra'         => '',
            'url_pattern'   => isset($moduleData['urlPatterns']) ?
                (is_array($moduleData['urlPatterns']) ?
                    ($moduleData['urlPatterns'][$slug] ?? '') :
                    $moduleData['urlPatterns']) :
                $slug,
            'in_sitemap'    => $moduleData['inSitemap'] ?? 0,
            'active'        => 1,
            'created_by_id' => 1,
            'updated_by_id' => 0
        ];

        $parentId = $MM->insert($module);

        if ( ! empty($moduleData['subModules'] ?? [])) {
            $subModules = [];
            foreach ($moduleData['subModules'] as $group) {
                $subName      = self::prepName($group);
                $subModules[] = [
                    'parent'        => $parentId,
                    'is_core'       => 0,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'key'           => $slug . '.' . $group,
                    'slug'          => $group,
                    'class_name'    => '',
                    'name'          => $name . '.module.title.' . $subName,
                    'version'       => $version,
                    'description'   => $name . '.module.title.' . $subName,
                    'extra'         => '',
                    'url_pattern'   => $moduleData['urlPatterns'][$group] ?? '',
                    'in_sitemap'    => $moduleData['inSitemap'] ?? 0,
                    'active'        => 1,
                    'created_by_id' => 1,
                    'updated_by_id' => 0
                ];
            }

            $MM->insertBatch($subModules);
        }

        array_unshift($moduleData['subModules'], $slug);

        $modules = $MM->select(['id', 'slug', 'parent'])->whereIn('slug', $moduleData['subModules'])->findAll();

        $roles = $RM->whereIn('role', $moduleData['roles'])->findColumn('id');

        $permissions = [];

        foreach ($roles as $role) {
            foreach ($modules as $module) {
                $permissions[] = [
                    'role_id'       => $role,
                    'parent'        => $module->parent,
                    'module_id'     => $module->id,
                    'is_module'     => 1,
                    'is_system'     => 0,
                    'is_plugin'     => 0,
                    'slug'          => $module->slug,
                    'access'        => 1,
                    'self'          => 0,
                    'create'        => 1,
                    'read'          => 1,
                    'update'        => 1,
                    'delete'        => 1,
                    'moderated'     => 0,
                    'settings'      => 1,
                    'extra'         => '',
                    'created_by_id' => 1,
                    'updated_by_id' => 0
                ];
            }
        }

        $PM->insertBatch($permissions);

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
     * @return mixed
     * @throws ReflectionException
     */
    public static function createModulePage(
        string $key,
        ?string $title = null,
        ?string $url = null,
        ?int $parent = null,
        ?string $slug = null
    ): mixed {
        $meta = self::meta($key);

        $metaId = model(MetaDataModel::class)->insert(
            [
                'parent'          => $parent ?? (($meta['parent'] != 0) ? $meta['parent'] : 1),
                'locale_id'       => 1, // TODO сделать настраиваемой
                'module_id'       => $meta['id'] ?? $parent,
                'slug'            => $slug ?? $meta['slug'],
                'creator_id'      => 1,
                'item_id'         => 0,
                'title'           => $title ?? $meta['name'],
                'url'             => $url ?? $meta['url'],
                'meta'            => '',
                'status'          => MetaStatuses::Publish->value,
                'meta_type'       => MetaDataTypes::Module->value,
                'in_sitemap'      => $meta['inSitemap'] ?? 0,
                'use_url_pattern' => 0,
                'created_by_id'   => 1
            ]
        );

        if ($metaId) {
            model(ContentModel::class)->insert(['id' => $metaId]);
        }

        return $metaId;
    }
}