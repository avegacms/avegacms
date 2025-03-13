<?php

declare(strict_types=1);

namespace AvegaCms\Utilities;

use AvegaCms\Config\Services;
use AvegaCms\Enums\FieldsReturnTypes;
use AvegaCms\Enums\MetaDataTypes;
use AvegaCms\Enums\MetaStatuses;
use AvegaCms\Models\Admin\ContentModel;
use AvegaCms\Models\Admin\MetaDataModel;
use AvegaCms\Models\Admin\SettingsModel;
use ReflectionException;
use RuntimeException;

class Cms
{
    private static ?object $access   = null;
    private static ?object $userData = null;

    /**
     * @throws ReflectionException
     */
    public static function createPage(
        string $title,
        ?string $url,
        ?string $slug = null,
        ?int $parent = null,
        ?int $localeId = null,
        ?int $moduleId = null,
        bool $inSitemap = false
    ): mixed {
        $metaId = model(MetaDataModel::class)->insert(
            [
                'parent'          => $parent ?? 1,
                'locale_id'       => $localeId ?? 1,
                'module_id'       => $moduleId ?? 0,
                'slug'            => $slug ?? '',
                'creator_id'      => 1,
                'item_id'         => 0,
                'title'           => $title,
                'url'             => $url ?? '',
                'meta'            => [],
                'status'          => MetaStatuses::Publish->name,
                'meta_type'       => MetaDataTypes::Page->name,
                'in_sitemap'      => $inSitemap,
                'use_url_pattern' => false,
                'created_by_id'   => 1,
            ]
        );

        if ($metaId) {
            model(ContentModel::class)->insert(['id' => $metaId]);
        }

        return $metaId;
    }

    public static function userData(): ?object
    {
        return self::$userData;
    }

    public static function userPermission(): ?object
    {
        return self::$access;
    }

    public static function setUser(string $key, ?object $value = null): void
    {
        match ($key) {
            'user'       => self::$userData = $value ?? null,
            'permission' => self::$access   = $value ?? null
        };
    }

    public static function initClientSession(array $data = []): void
    {
        $session = Services::session();
        if (! $session->has('avegacms')) {
            $session->set(
                'avegacms',
                [
                    'client' => [
                        'locale'  => $data['client']['locale'] ?? null,
                        'user'    => $data['client']['user'] ?? null,
                        'confirm' => [
                            'useCookie' => null,
                            'gdpr'      => null,
                        ],
                    ],
                    'modules' => null,
                    'admin'   => null,
                ]
            );
        }
    }

    /**
     * @throws ReflectionException|RuntimeException
     */
    public static function settings(string $key, array|bool|int|string|null $value = null, ?array $config = []): mixed
    {
        [$entity, $slug, $property] = self::parseKey($key);

        $prefix = 'settings_';

        $SM = model(SettingsModel::class);

        if ($value === null) {
            $settings = cache()->remember($prefix . $entity, DAY * 30, static function () use ($entity, $SM) {
                if (empty($settings = $SM->getSettings($entity))) {
                    throw new RuntimeException('Unable to find a Settings array in DB.');
                }

                $processArray = static function (&$settings) use (&$processArray) {
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

            if (null !== $slug && null !== $property) {
                if (! isset($settings[$slug][$property])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key.');
                }
                $settings = $settings[$slug][$property];
            } elseif (null !== $slug) {
                if (! isset($settings[$slug])) {
                    throw new RuntimeException('Unable to find in Settings array slug/key');
                }
                $settings = $settings[$slug];
            }

            return $settings;
        }
        if (($id = $SM->getId($entity, $slug, $property)) > 0) {
            return $SM->update($id, ['value' => $value]);
        }

        return $SM->insert(
            [
                'locale_id'     => $config['locale_id'] ?? 0,
                'module_id'     => $config['module_id'] ?? 0,
                'is_core'       => (bool) ($config['is_core'] ?? 0),
                'entity'        => $entity,
                'slug'          => $slug ?? '',
                'key'           => $property ?? '',
                'value'         => $value,
                'default_value' => $config['default_value'] ?? '',
                'return_type'   => $config['return_type'] ?? FieldsReturnTypes::String->value,
                'label'         => $config['label'] ?? '',
                'context'       => $config['context'] ?? '',
                'sort'          => $config['sort'] ?? 100,
            ]
        );
    }

    public static function urlPattern(
        string $url,
        bool $usePattern,
        int|string $id,
        string $slug,
        int|string $localeId,
        int|string $parent
    ): string {
        return
            base_url(
                strtolower(
                    $usePattern === true ?
                        str_ireplace(
                            ['{id}', '{slug}', '{locale_id}', '{parent}'],
                            [$id, $slug, $localeId, $parent],
                            $url
                        ) :
                        $url
                )
            );
    }

    public static function arrayToObject(array $array): object
    {
        return (object) array_map(static function ($item) {
            if (is_array($item)) {
                return self::arrayToObject($item);
            }

            return $item;
        }, $array);
    }

    public static function getTree(array $data): array
    {
        $tree = [];

        foreach ($data as $key => &$item) {
            if (isset($item['parent'])) {
                if (! $item['parent']) {
                    $tree[$key] = &$item;
                } else {
                    $data[$item['parent']]['list'][$key] = &$item;
                }
            }
        }

        return $tree;
    }

    /**
     * @throws RuntimeException
     */
    public static function parseKey(string $key): array
    {
        if (count($parts = explode('.', $key)) === 0) {
            throw new RuntimeException('$key cannot be empty');
        }

        $parts[1] ??= null;
        $parts[2] ??= null;

        return $parts;
    }

    public static function castAs(mixed $value, string $type): mixed
    {
        return match ($type) {
            FieldsReturnTypes::Integer->value => (int) $value,
            FieldsReturnTypes::Double->value,
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
            default => null
        };
    }
}
