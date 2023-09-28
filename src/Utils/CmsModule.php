<?php

namespace AvegaCms\Utils;

use AvegaCms\Config\AvegaCms;
use AvegaCms\Entities\{ModulesEntity, PermissionsEntity};
use AvegaCms\Models\Admin\{ModulesModel, PermissionsModel, RolesModel};
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

        $name = ucwords($moduleData['moduleSlug']);
        $slug = strtolower($moduleData['moduleSlug']);

        $module = [
            'parent'        => 0,
            'is_core'       => 0,
            'is_plugin'     => 0,
            'is_system'     => 0,
            'slug'          => $slug,
            'name'          => $name . '.module.title.main',
            'version'       => $version,
            'description'   => $name . '.module.title.main',
            'extra'         => '',
            'in_sitemap'    => 0,
            'active'        => 1,
            'created_by_id' => 1,
            'updated_by_id' => 0
        ];

        $parentId = $MM->insert((new ModulesEntity($module)));

        if ( ! empty($moduleData['subModules'])) {
            $subModules = [];
            foreach ($moduleData['subModules'] as $group) {
                $subName      = self::prepName($group);
                $subModules[] = (new ModulesEntity([
                    'parent'        => $parentId,
                    'is_core'       => 0,
                    'is_plugin'     => 0,
                    'is_system'     => 0,
                    'slug'          => $group,
                    'name'          => $name . '.module.title.' . $subName,
                    'version'       => $version,
                    'description'   => $name . '.module.title.' . $subName,
                    'extra'         => '',
                    'in_sitemap'    => 0,
                    'active'        => 1,
                    'created_by_id' => 1,
                    'updated_by_id' => 0
                ]));
            }

            $MM->insertBatch($subModules);
        }

        array_unshift($moduleData['subModules'], $slug);

        $modules = $MM->select(['id', 'slug', 'parent'])->whereIn('slug', $moduleData['subModules'])->findAll();

        $roles = $RM->whereIn('role', $moduleData['roles'])->findColumn('id');

        $permissions = [];

        foreach ($roles as $role) {
            foreach ($modules as $module) {
                $permissions[] = (new PermissionsEntity([
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
                ]));
            }
        }

        $PM->insertBatch($permissions);
    }

    public static function prepName(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))));
    }

    /**
     * @param  string  $key
     * @return array|null
     */
    public static function meta(string $key): array
    {
        [$module, $subModule] = self::parseKey($key);
        $meta = model(ModulesModel::class)->ModulesModel();

        if (($meta = ! is_null($subModule) ? $meta[$module][$subModule] ?? null : ($meta[$module] ?? null)) === null) {
            throw new RuntimeException('Module metadata not found');
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

        return $parts;
    }
}