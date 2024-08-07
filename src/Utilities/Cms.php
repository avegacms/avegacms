<?php

declare(strict_types = 1);

namespace AvegaCms\Utilities;

use AvegaCms\Enums\{MetaDataTypes, MetaStatuses, FieldsReturnTypes};
use AvegaCms\Models\Admin\{ContentModel, MetaDataModel, SettingsModel};
use AvegaCms\Config\Services;
use RuntimeException;
use ReflectionException;

class Cms
{
    private static object|null $access   = null;
    private static object|null $userData = null;

    /**
     * @param  string  $title
     * @param  string|null  $url
     * @param  string|null  $slug
     * @param  int|null  $parent
     * @param  int|null  $localeId
     * @param  int|null  $inSitemap
     * @return mixed
     * @throws ReflectionException
     */
    public static function createPage(
        string $title,
        ?string $url,
        ?string $slug = null,
        ?int $parent = null,
        ?int $localeId = null,
        ?int $inSitemap = null
    ): mixed {
        $metaId = model(MetaDataModel::class)->insert(
            [
                'parent'          => $parent ?? 1,
                'locale_id'       => $localeId ?? 1,
                'module_id'       => 0,
                'slug'            => $slug ?? '',
                'creator_id'      => 1,
                'item_id'         => 0,
                'title'           => $title,
                'url'             => $url ?? '',
                'meta'            => '',
                'status'          => MetaStatuses::Publish->name,
                'meta_type'       => MetaDataTypes::Page->name,
                'in_sitemap'      => $inSitemap ?? 1,
                'use_url_pattern' => 0,
                'created_by_id'   => 1
            ]
        );

        if ($metaId) {
            model(ContentModel::class)->insert(['id' => $metaId]);
        }

        return $metaId;
    }

    /**
     * @return object|null
     */
    public static function userData(): object|null
    {
        return self::$userData;
    }

    /**
     * @return object|null
     */
    public static function userPermission(): object|null
    {
        return self::$access;
    }

    /**
     * @param  string  $key
     * @param  object|null  $value
     * @return void
     */
    public static function setUser(string $key, ?object $value = null): void
    {
        match ($key) {
            'user'       => self::$userData = $value ?? null,
            'permission' => self::$access = $value ?? null
        };
    }

    /**
     * @param  array  $data
     * @return void
     */
    public static function initClientSession(array $data = []): void
    {
        $session = Services::session();
        if ( ! $session->has('avegacms')) {
            $session->set('avegacms',
                [
                    'client'  => [

                        'locale'  => $data['client']['locale'] ?? null,
                        'user'    => $data['client']['user'] ?? null,
                        'confirm' => [

                            'useCookie' => null,
                            'gdpr'      => null
                        ]
                    ],
                    'modules' => null,
                    'admin'   => null
                ]
            );
        }
    }

    /**
     * @param  string  $key
     * @param  string|array|int|bool|null  $value
     * @param  array|null  $config
     * @return mixed
     * @throws RuntimeException|ReflectionException
     */
    public static function settings(string $key, string|array|int|bool|null $value = null, ?array $config = []): mixed
    {
        [$entity, $slug, $property] = self::parseKey($key);

        $prefix = 'settings_';

        $SM = model(SettingsModel::class);

        if ($value === null) {
            $settings = cache()->remember($prefix . $entity, DAY * 30, function () use ($entity, $SM) {
                if (empty($settings = $SM->getSettings($entity))) {
                    throw new RuntimeException('Unable to find a Settings array in DB.');
                }

                $processArray = function (&$settings) use (&$processArray) {
                    foreach ($settings as $key => &$item) {
                        if (is_array($item)) {
                            if (isset($item['return_type'])) {
                                $rt = $item['return_type'];
                                unset($item['return_type']);
                                $settings[$key] = self::castAs($item['value'], $rt);
                            } else {
                                $processArray($item);
                            }
                        }
                    }
                };

                $processArray($settings);

                return $settings;
            });

            if ( ! is_null($slug) && ! is_null($property)) {
                if ( ! isset($settings[$slug][$property])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key.');
                }
                $settings = $settings[$slug][$property];
            } elseif ( ! is_null($slug)) {
                if ( ! isset($settings[$slug])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key');
                }
                $settings = $settings[$slug];
            }

            return $settings;
        } else {
            if (($id = $SM->getId($entity, $slug, $property)) > 0) {
                return $SM->update($id, ['value' => $value]);
            } else {
                return $SM->insert(
                    [
                        'locale_id'     => $config['locale_id'] ?? 0,
                        'module_id'     => $config['module_id'] ?? 0,
                        'is_core'       => boolval($config['is_core'] ?? 0),
                        'entity'        => $entity,
                        'slug'          => $slug ?? '',
                        'key'           => $property ?? '',
                        'value'         => $value,
                        'default_value' => $config['default_value'] ?? '',
                        'return_type'   => $config['return_type'] ?? FieldsReturnTypes::String->value,
                        'label'         => $config['label'] ?? '',
                        'context'       => $config['context'] ?? '',
                        'sort'          => $config['sort'] ?? 100
                    ]
                );
            }
        }
    }

    /**
     * @param  string  $url
     * @param  int|string  $usePattern
     * @param  int|string  $id
     * @param  string  $slug
     * @param  int|string  $locale_id
     * @param  int|string  $parent
     * @return string
     */
    public static function urlPattern(
        string $url,
        int|string $usePattern,
        int|string $id,
        string $slug,
        int|string $localeId,
        int|string $parent
    ): string {
        return
            base_url(
                strtolower(
                    $usePattern == 1 ?
                        str_ireplace(
                            ['{id}', '{slug}', '{locale_id}', '{parent}'],
                            [$id, $slug, $localeId, $parent],
                            $url
                        ) :
                        $url
                )
            );
    }

    /**
     * @param  array  $array
     * @return object
     */
    public static function arrayToObject(array $array): object
    {
        return (object) array_map(function ($item) {
            if (is_array($item)) {
                return self::arrayToObject($item);
            }
            return $item;
        }, $array);
    }

    /**
     * @param  array  $data
     * @return array
     */
    public static function getTree(array $data): array
    {
        $tree = [];

        foreach ($data as $key => &$item) {
            if (isset($item['parent'])) {
                if ( ! $item['parent']) {
                    $tree[$key] = &$item;
                } else {
                    $data[$item['parent']]['list'][$key] = &$item;
                }
            }
        }

        return $tree;
    }

    /**
     * @param  string  $key
     * @return array
     * @throws RuntimeException
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

    /**
     * @param $value
     * @param  string  $type
     * @return integer|float|string|boolean|array|null
     */
    public static function castAs($value, string $type): mixed
    {
        return match ($type) {
            FieldsReturnTypes::Integer->value => (int) $value,
            FieldsReturnTypes::Double->value  => (double) $value,
            FieldsReturnTypes::Float->value   => (float) $value,
            FieldsReturnTypes::String->value  => (string) $value,
            FieldsReturnTypes::Boolean->value => (bool) $value,
            FieldsReturnTypes::Json->value    => $value,
            FieldsReturnTypes::Array->value   => (array) (
            (
            (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:'))) ?
                unserialize($value) :
                $value
            )
            ),
            default                           => null
        };
    }
}